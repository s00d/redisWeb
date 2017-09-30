<?php
namespace App;
use Noodlehaus\AbstractConfig;

/**
 * Config for Slim.
 */

class Config extends AbstractConfig
{

    /**
     * Create new Config service provider.
     *
     * @param string|array $configInput
     */
    public function __construct($configInput)
    {
        parent::__construct($configInput);
    }

    /**
     * Config middleware invokable class.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param \Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param callable                                 $next     Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke($request, $response, $next)
    {
        return $next($request, $response);
    }

}