Redis-WEB
=========

Redis-web is a simple web interface in [Slim](https://github.com/slimphp/Slim)
and [Vue](https://github.com/vuejs/vue) to manage [Redis](http://redis.io/) databases.

It is released under the
[Creative Commons Attribution 3.0 license](http://creativecommons.org/licenses/by/3.0/).
This code is being developed and maintained by [Pavel Kuzmin](https://github.com/s00d/).

You can send comments, patches, questions
[here on github](https://github.com/s00d/redisWeb/issues)

Special thanks to [Erik Dubbelboer](https://github.com/ErikDubbelboer/)

Preview
=======

<p align="center">
  <img width="600" height="400" src="https://image.prntscr.com/image/5YRJw7Q2RX6g_xVAtZED6A.png"><br>
  <img width="600" height="400" src="https://image.prntscr.com/image/Zq6zI-4STlqFAHADFJgW1Q.png">
</p>



Installing/Configuring
======================

To install [redis-web](https://packagist.org/packages/s00d/redis-web) through [composer](http://getcomposer.org/) you need to execute the following commands:

```
php composer.phar create-project -s dev s00d/redis-web path/to/install
```

or you can also do a manual install using:

```
git clone https://github.com/s00d/redisWeb.git
cd redisWeb
```

Install and run:

```
composer install && composer update
npm i
npm prod
```



Settings
========
Copy ``.env.example`` to ``.env`` and edit it with your specific configuration.

all setting in file ``./Settings.php``

#### Passwords:

Cleartext passwords are only good for quick testing. You probably want to use hashed passwords. Hashed password can be generated with htpasswd command line tool or password_hash() PHP function
```
$ htpasswd -nbBC 10 root password
root:$2y$10$ZDEIUCQ7BSDLP3d2MI4HIOI4.CcaYqRj8ICCyJT2isOBd5JLM7zYe
```

Add to settings
```
"users" => [
  "root" => '$2y$10$ZDEIUCQ7BSDLP3d2MI4HIOI4.CcaYqRj8ICCyJT2isOBd5JLM7zYe'
]
```


#### Apache configuration
Ensure your ``.htaccess`` and ``index.php`` files are in the same public-accessible directory. The ``.htaccess`` file should contain this code:
```
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [QSA,L]
```
Make sure your Apache virtual host is configured with the AllowOverride option so that the .htaccess rewrite rules can be used:
```
AllowOverride All
```

#### Nginx configuration
This is an example Nginx virtual host configuration for the domain ``example.com``. It listens for inbound HTTP connections on port 80. It assumes a PHP-FPM server is running on port 9000.
You should update the ``server_name``, ``error_log``, ``access_log``, and ``root directives`` with your own values.
```
server {
    listen 80;
    server_name example.com;
    index index.php;
    error_log /path/to/example.error.log;
    access_log /path/to/example.access.log;
    root /path/to/public;

    location / {
        try_files $uri /index.php$is_args$args;
    }

    location ~ \.php {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param SCRIPT_NAME $fastcgi_script_name;
        fastcgi_index index.php;
        fastcgi_pass 127.0.0.1:9000;
    }
}
```

Queues!
=======
Show all queues from redis:

<p align="center">
  <img width="300" height="150" src="https://image.prntscr.com/image/9yn9HktGRL_SznmjyWqn9w.png"><br>
  </p>



Install latest PM2 stable version is installable via NPM:
```
npm install pm2@latest -g
```

Run

```
npm run socket:start
```