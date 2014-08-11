#DESCRIPTION
Wapistrano is a web user interface dedicated to configure, store and execute capistrano tasks.
It's mainly built on Symfony 2 framework and uses the queue manager Gearman listened by python workers.
Overview:
--- Webui part (symfony2, web server etc.)
--- Queue manager part (gearman)
--- Workers part (python and capistrano)

These three parts can run on the same station or separately on their own environment.

#INSTALL
##On the webserver side (where the wapistrano webui will live)

###Prerequisite:
* a webserver (Apache, Nginx),
* Mysql server,
* php5.4.1,
* php5-dev,
* php-pear,
* php-redis (git clone git@github.com:nicolasff/phpredis.git;cd phpredis; phpize;./configure;make && make install;echo "extension=redis.so" >> /etc/php5/conf.d/redis.ini),
* libgearman (apt-get install libgearman-dev),
* php-gearman 0.8.3 (pecl install channel://pecl.php.net/gearman-0.8.3; echo "extension=gearman.so" >> /etc/php5/conf.d/gearman.ini; service apache2 restart)
* Git

###Cloning and installation

```
git clone git@github.com:c2is/wapistrano.git
cd wapistrano
curl -sS https://getcomposer.org/installer | php
composer.phar install
./app/console doctrine:schema:update --force
chmod -R 777 ./app/cache
chmod -R 777 ./app/logs
```

##On the gearman server side (where the gearman daemon will run)
Just install gearman :-)
```
apt-get install gearman
```

##On the capistrano server side (where capistrano and python workers will live)
Capistrano install:
```
apt-get install capistrano
```
Wapistrano needs capistrano/ext/multistage installed:
```
gem install capistrano-ext
```

Python-setuptools install (to get easy_install):

```
apt-get install python-setuptools
```

Redis, Python-redis and Python-gearman packages installs:
```
apt-get install redis-server
easy_install redis
#command above will install this package https://github.com/Yelp/python-gearman
easy_install gearman
```

Install wapyd:
```
Please, report to wapyd project
```

#MIGRATION FROM WEBISTRANO TO WAPISTRANO
Export your capistrano db like this:
```
mysqldump -u root -p webistrano_prod --no-create-info -c > /tmp/webistrano-data.sql
```

Perform fields names changes to map new database structure:
```
sed -i -e "s/\`recipe_id\`, \`stage_id\`/\`recipes_id\`, \`stages_id\`/g" /tmp/webistrano-data.sql
```

Import the resulting sql file:
```
mysql -h 127.0.0.1 -u root wapistrano < /tmp/webistrano-data.sql
```


*** Exclusive admin Grants***
Add/Edit recipe
Add/Edit user
Add/Edit host

*** Todo ***
- command to check if env is ok (services installed, capistrano path and rights ok etc.)
- status column to fill in projects list

