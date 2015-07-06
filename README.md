[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/fkooman/oauth/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/fkooman/oauth/?branch=master)

# Introduction
Very simple OAuth 2.0 authorization server.

# Apache
Place this in `/etc/httpd.d

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

