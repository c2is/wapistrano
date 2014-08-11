#INSTALL
##On the webserver side (where the wapistrano webui will live)

###Prerequisite:
* a webserver (Apache, Nginx),
* Mysql server,
* php5.4.1,
* php-pear
* php-redis (git clone git@github.com:nicolasff/phpredis.git;cd phpredis; phpize;./configure;make && make install;echo "extension=redis.so" >> /etc/php5/conf.d/redis.ini),
* libgearman (apt-get install libgearman-dev)
* make,
* php-gearman 0.8.3 (pecl install channel://pecl.php.net/gearman-0.8.3; echo "extension=gearman.so" >> /etc/php5/conf.d/gearman.ini; service apache2 restart)
* Git

###Cloning and installation

```
git clone git@gitlab.c2is.fr:a.cianfarani/wapistrano.git wapistrano
cd wapistrano
curl -sS https://getcomposer.org/installer | php
composer.phar install
./app/console doctrine:schema:update --force
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
apt-get install redis
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

