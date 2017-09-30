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

Copy .env.example to .env and edit it with your specific configuration.

Install and run:

```
composer install && composer update
npm i
npm prod
```



Settings
========
all setting in file ``./Settings.php``

Passwords:

Cleartext passwords are only good for quick testing. You probably want to use hashed passwords. Hashed password can be generated with htpasswd command line tool or password_hash() PHP function
```
$ htpasswd -nbBC 10 root password
root:$2y$10$ZDEIUCQ7BSDLP3d2MI4HIOI4.CcaYqRj8ICCyJT2isOBd5JLM7zYe
```


