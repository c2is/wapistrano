FROM debian:stretch

MAINTAINER André Cianfarani <a.cianfarani@c2is.fr>

RUN apt-get update && apt-get install -y apache2 libapache2-mod-php7.0 php7.0 php7.0-mysql php7.0-curl php7.0-gd php7.0-intl php7.0-mcrypt php7.0-xmlrpc php7.0-zip php7.0-dom php7.0-dev
RUN apt-get install -y ssmtp
RUN apt-get install -y redis-server
RUN apt-get install -y gearman
RUN apt-get install -y libgearman7 libgearman-dev
ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
ENV APACHE_LOG_DIR /var/log/apache2
RUN mkdir /etc/apache2/ssl
RUN a2enmod rewrite
RUN a2enmod ssl
RUN a2enmod headers

# Install git
RUN apt-get update && apt-get install -y git

# Install composer
RUN apt-get update && apt-get install -y curl
RUN curl -s https://getcomposer.org/installer | php
RUN mv composer.phar /usr/local/bin/composer
RUN mkdir /var/composer
RUN composer require symfony/var-dumper --working-dir=/var/composer
RUN chown -R www-data:www-data /var/composer

RUN git clone https://github.com/phpredis/phpredis.git
RUN cd phpredis; phpize;./configure;make && make install;echo "extension=redis.so" >> /etc/php/7.0/mods-available/redis.ini
RUN ln -sf /etc/php/7.0/mods-available/redis.ini /etc/php/7.0/apache2/conf.d/20-redis.ini
RUN ln -sf /etc/php/7.0/mods-available/redis.ini /etc/php/7.0/cli/conf.d/20-redis.ini

RUN git clone https://github.com/wcgallego/pecl-gearman.git
RUN cd pecl-gearman; phpize;./configure;make && make install;echo "extension=gearman.so" >> /etc/php/7.0/mods-available/gearman.ini
RUN ln -sf /etc/php/7.0/mods-available/gearman.ini /etc/php/7.0/apache2/conf.d/40-gearman.ini
RUN ln -sf /etc/php/7.0/mods-available/gearman.ini /etc/php/7.0/cli/conf.d/40-gearman.ini

ADD ports.conf /etc/apache2/ports.conf
ADD ssl.key /etc/apache2/ssl/ssl.key
ADD ssl.crt /etc/apache2/ssl/ssl.crt
COPY entrypoint.sh /entrypoint.sh
RUN chmod 777 /entrypoint.sh
ENTRYPOINT ["/entrypoint.sh"]

RUN ln -s /etc/apache2/sites-available/vhost-website.conf /etc/apache2/sites-enabled/vhost-website.conf
CMD ["/usr/sbin/apache2ctl", "-D", "FOREGROUND"]
#CMD ["/usr/sbin/gearmand start"]
EXPOSE 80
EXPOSE 443
