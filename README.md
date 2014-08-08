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

###Commands to execute

```
git clone git@gitlab.c2is.fr:a.cianfarani/wapistrano.git wapistrano
cd wapistrano
curl -sS https://getcomposer.org/installer | php
composer.phar install
./app/console doctrine:schema:update --force
```

#MIGRATE FROM WEBISTRANO TO WAPISTRANO
Export your capistrano db like this:
```
mysqldump -u root -p webistrano_prod --no-create-info -c > /tmp/webistrano-data.sql
```

Perform a fields names changes to map new database structure:
```
sed -i -e "s/\`recipe_id\`, \`stage_id\`/\`recipes_id\`, \`stages_id\`/g" /tmp/webistrano-data.sql
```

Import the resulting sql file:
```
mysql -h 127.0.0.1 -u root wapistrano < /tmp/webistrano-data.sql
```


##On the gearman server side (where the gearman daemon will run)
Just install gearman :-)
```
apt-get install gearman
```

##On the capistrano server side (where capistrano and python workers will live)
Install redis
```
apt-get install redis
```

Install wapyd
```

```




#Webui side
A webserver (Apache, Nginx)
php-redis
php-gearman 1.1
mysql


#Capistrano server side
python 2.7
python-gearman (sudo easy_install gearman) (https://github.com/Yelp/python-gearman)
redis (sudo easy_install redis)
Capistrano (Need capistrano/ext/multistage installed : gem install capistrano-ext)
Git


#Somewhere on an accessible server
gearman
redis

*** Exclusive admin Grants***
Add/Edit recipe
Add/Edit user
Add/Edit host

*** Todo ***
- command to check if env is ok (services installed, capistrano path and rights ok etc.)
- status column to fill in projects list

