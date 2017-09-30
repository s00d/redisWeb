<?php
namespace App;
use \Psr\Http\Message\RequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class IpFilterMiddleware
{
    protected $ips_allow = [];
    protected $allowed = null;
    protected $handler = null;
    public function __construct($ips_allow = []){
        if(is_array($ips_allow)) foreach ($ips_allow as $address) {
            if (is_array($address)) $this->addIpRangeAllow($address[0], $address[1]); else $this->addIpAllow($address);
        } else if (is_string($ips_allow)) $this->ips_allow[] = $this->addIpAllow($ips_allow);
        else if (is_numeric($ips_allow)) $this->ips_allow[] = $ips_allow;


        $this->handler = function (Request $request, Response $response) {
            $response = $response->withStatus(401);
            $response->getBody()->write("Access denied");
            return $response;
        };
    }

    public function __invoke(Request $request, Response $response, $next) {
        if(!empty($this->ips_allow)) $this->allowed = $this->allow();
        else $this->allowed = true;

        if (!$this->allowed) {
            $handler = $this->handler;
            return $handler($request, $response);
        }
        $response = $next($request, $response);
        return $response;
    }

    private function merge_ip($array, $new) {
        $new = explode(':', $new);
        foreach ($new as &$ip) $ip = ip2long($ip);
        return array_merge($array, $new);
    }

    private  function get_client_ip_server() {
        $ipaddress = [];
        if (isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $this->merge_ip($ipaddress, $_SERVER['REMOTE_ADDR']);
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $this->merge_ip($ipaddress, $_SERVER['HTTP_CLIENT_IP']);
        if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $this->merge_ip($ipaddress, $_SERVER['HTTP_X_FORWARDED_FOR']);
        if(isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $this->merge_ip($ipaddress, $_SERVER['HTTP_X_FORWARDED']);
        if(isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $this->merge_ip($ipaddress, $_SERVER['HTTP_FORWARDED_FOR']);
        if(isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $this->merge_ip($ipaddress, $_SERVER['HTTP_FORWARDED']);


        return $ipaddress;
    }

    public function allow() {
        $ips = $this->get_client_ip_server();
        return !empty(array_intersect($this->ips_allow, $ips));
    }

    public function setHandler($handler) {
        $this->handler = $handler;
    }

    public function addIpRangeAllow($start, $end) {
        foreach (range(ip2long($start), ip2long($end)) as $address)
            $this->ips_allow[] = $address;
        return $this;
    }

    public function addIpAllow($ip) {
        $this->ips_allow[] = ip2long($ip);
        return $this;
    }
}