[![Build Status](https://travis-ci.org/fkooman/oauth.svg)](https://travis-ci.org/fkooman/oauth)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/fkooman/oauth/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/fkooman/oauth/?branch=master)

# Introduction
Very simple OAuth 2.0 authorization server.

**WORK IN PROGRESS**

# Development
We assume that your web server runs under the `apache` user and your user 
account is called `fkooman` in group `fkooman`.

    $ cd /var/www
    $ sudo mkdir oauth
    $ sudo chown fkooman.fkooman oauth
    $ git clone https://github.com/fkooman/oauth.git
    $ cd oauth
    $ /path/to/composer.phar install
    $ mkdir -p data
    $ sudo chown -R apache.apache data
    $ sudo semanage fcontext -a -t httpd_sys_rw_content_t '/var/www/oauth/data(/.*)?'
    $ sudo restorecon -R /var/www/oauth/data
    $ cp config/server.ini.example config/server.ini

Now to initialize the database:

    $ sudo -u apache php bin/oauth-init-db.php

# Apache
Place this in `/etc/httpd/conf.d/oauth.conf`:

    Alias /oauth /var/www/oauth/web

    <Directory /var/www/oauth/web>
        AllowOverride None

        Require local
        #Require all granted

        RewriteEngine on
        RewriteBase /oauth
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteRule ^(.*)$ index.php/$1 [L,QSA]

        SetEnvIfNoCase ^Authorization$ "(.+)" HTTP_AUTHORIZATION=$1
    </Directory>

# Configuration
## Server
The server configuration is done in `config/server.ini`. 

## Users
Currently only Basic authentication is supported for user authentication. You
can configure users in `config/users.json`, an example file:

    {
        "admin": {
            "secret": "$2y$10$9jz\/zsSxKh0cy57r4j1\/O.Eq.PmRNrhWKL53SSKKaIrFaJd7zAJaO"
        }
    }

The `secret` field is the output of `password_hash()`. You can use the script 
`bin/passwordHash.php` to generate a hash for your chosen secret.

## Clients
Currently no client registration implemented. Only the 
"Authorization Code Grant" 
([section 4.1 of RFC 6749](https://tools.ietf.org/html/rfc6749#section-4.1)) 
is implemented.

The client registration will be very similar to the Resource Server 
registration.

## Resource Servers
Add resource servers to `config/resource_servers.json`, an example file:

    {
        "my_resource_server": {
            "scope": "post",
            "secret": "$2y$10$cG3iFTTpitGAHYyci8bII.68.uRwvmSpCTvEfVmDwka5E2132XmAC"
        }
    }

The `secret` field is the output of `password_hash()`. You can use the script 
`bin/oauth-password-hash.php` to generate a hash for your chosen secret. The 
`scope` field contain the scope valus (space separated) supported by the 
resource server.

# Endpoints
There are three endpoints defined:
* `/authorize`
* `/token`
* `/introspect`

The OAuth clients will use the `/authorize` and `/token` endpoints. The 
protocol is described in [RFC 6749](https://tools.ietf.org/html/rfc6749). 
Resource servers will use the `/introspect` endpoint to validate the access 
tokens used by the clients. Resource servers need to be registered and use 
Basic authentication to validate access tokens. The protocol is described in 
[draft-ietf-oauth-introspection-11.txt](https://tools.ietf.org/html/draft-ietf-oauth-introspection).
