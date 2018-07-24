#!/bin/bash
set -e
if [ -f /etc/apache2/sites-available/vhost-website.conf ]; then
 rm /etc/apache2/sites-available/vhost-website.conf
fi

if [ -z "$WEBSITE_HOST" ]; then
	WEBSITE_HOST="website.docker"
fi
if [ "$SYMFONY_VHOST_COMPLIANT" == "yes" ]; then
	SUFFIX="/web"
fi

if [ -f /etc/ssmtp/revaliases ]; then
    rm /etc/ssmtp/revaliases
fi
if [ -f /etc/ssmtp/ssmtp.conf ]; then
    rm /etc/ssmtp/ssmtp.conf
fi

cat <<EOF >> /etc/ssmtp/revaliases
root:$SMTP_USER:$SMTP_HOST
EOF

cat <<EOF >> /etc/ssmtp/ssmtp.conf
root=postmaster
mailhub=$SMTP_HOST
AuthUser=$SMTP_USER
AuthPass=$SMTP_PASSWORD
FromLineOverride=YES
#Debug=YES
hostname=docker.acti
EOF

cat <<EOF >> /etc/apache2/sites-available/vhost-website.conf
<VirtualHost *:80>
        ServerName $WEBSITE_HOST
        ServerAlias *.$WEBSITE_HOST
        ServerAdmin webmaster@localhost
        DocumentRoot /var/www/website$SUFFIX

        ErrorLog ${APACHE_LOG_DIR}/error.log
        CustomLog ${APACHE_LOG_DIR}/access.log combined

        <Directory /var/www/website$SUFFIX>
          Require all granted	
          Options -Indexes +FollowSymLinks -MultiViews
          Order allow,deny
          allow from all
	      AllowOverride All
        </Directory>
</VirtualHost>
<VirtualHost *:443>
        ServerName $WEBSITE_HOST
        ServerAlias *.$WEBSITE_HOST
        ServerAdmin webmaster@localhost
        DocumentRoot /var/www/website$SUFFIX

        ErrorLog ${APACHE_LOG_DIR}/error-ssl.log
        CustomLog ${APACHE_LOG_DIR}/access-ssl.log combined

        <Directory /var/www/website$SUFFIX>
          Require all granted
          Options -Indexes +FollowSymLinks -MultiViews
          Order allow,deny
          allow from all
              AllowOverride All
        </Directory>

        SSLEngine on
        SSLCertificateFile    /etc/apache2/ssl/ssl.crt
        SSLCertificateKeyFile /etc/apache2/ssl/ssl.key
        BrowserMatch "MSIE [2-6]" \
                nokeepalive ssl-unclean-shutdown \
                downgrade-1.0 force-response-1.0
        BrowserMatch "MSIE [17-9]" ssl-unclean-shutdown
        RequestHeader set X-Forwarded-Proto "https"
</VirtualHost>
EOF

#if [ "$CERTIFICAT_CNAME" != "" ]; then
# openssl req -new -newkey rsa:4096 -days 365 -nodes -x509 -subj "/C=FR/ST=c2is/L=Lyon/O=c2is/CN=$CERTIFICAT_CNAME" -keyout /etc/apache2/ssl/ssl.key -out /etc/apache2/ssl/ssl.crt
#fi

exec "$@"
