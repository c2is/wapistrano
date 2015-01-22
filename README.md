WAPISTRANO - Alpha Version
====================

![Wapistrano project home page](./doc/shoot1.png?raw=true "Optional Title")

#Description
Wapistrano is a web user interface dedicated to configure, store and execute capistrano tasks.
It's mainly built on Symfony 2 framework and uses the queue manager Gearman listened by python workers.
Overview:

* Webui part (symfony2, web server etc.)
* Queue manager part (gearman)
* Workers part (python and capistrano)

These three parts can run on the same station or separately on their own environment.

Wapistrano is strongly based on [Webistrano](https://github.com/peritor/webistrano/) (which is unfortunately no longer maintained).
The Wapistrano database structure has been deliberately designed in order to be very closed to Webistrano structure to make Webistrano to Wapistrano migration easier.

#Warning
This is an Alpha version of this project. Many features and tests are missing.
It works with capistrano v 2.15.5 max, not yet with 3.* versions.

#Easy install
If you want a quick install to test wapistrano immediately, use our appliance. We built it under VirtualBox but you can use it with VmWare.

##Install appliance on Virtualbox
*  [Download the aplliance](https://www.dropbox.com/s/wir6v1t7e5iu9uo/wapistrano-debian.ova)
*  import it into VirtualBox, File->Import an appliance and choose the image you have juste downloaded
*  run the virtual machine
*  go to http://127.0.0.1:8074
*  log as admin (admin/admin) or as a user (user/user)

#Production install
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
apt-get install ruby
gem install capistrano -v 2.15.5
```
If you meet some problem on ssh auth with password, ensure that you have net-ssh <= 2.7.0 installed.
If not:

```
gem uninstall net-ssh
gem install net-ssh -v 2.7.0
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
If your python install uses site plugin, you have to install redis and gearman plugin under the wapyd user account (see below what is wapyd), you can do that by a classic plugin's install with --user option

```
python setup.py install --user
```
Install wapyd:

Please, report to [Wapy project](https://github.com/c2is/wapy)


#Migration from webistrano to wapistrano
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

#Features informations
## Exclusive admin rights
* Add/Edit recipe
* Add/Edit user
* Add/Edit host
* Create users
* Grant users/projects or projects/users

##Not yet implemented
* email notification on deploy
* disabled an host before deploying

