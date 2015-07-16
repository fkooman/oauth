[![Build Status](https://travis-ci.org/fkooman/oauth.svg)](https://travis-ci.org/fkooman/oauth)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/fkooman/oauth/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/fkooman/oauth/?branch=master)

# Introduction
Very simple OAuth 2.0 authorization server.

**WORK IN PROGRESS**

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
To generate a key for use in `config/server.ini`. You can use 
the script `bin/generateKey.php` to generate a key.

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

## Resource Servers
Add resource servers to `config/resource_servers.json`, an example file:

    {
        "my_resource_server": {
            "scope": "post",
            "secret": "$2y$10$cG3iFTTpitGAHYyci8bII.68.uRwvmSpCTvEfVmDwka5E2132XmAC"
        }
    }

The `secret` field is the output of `password_hash()`. You can use the script 
`bin/passwordHash.php` to generate a hash for your chosen secret. The 
`scope` field contain the scope valus (space separated) supported by the 
resource server.

## Clients
Currently no client registration implemented.

