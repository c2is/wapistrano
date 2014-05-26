*** Webui side ***
php-redis
php-gearman 1.1


*** Capistrano server side ***
python
python-gearman (sudo easy_install gearman) (https://github.com/Yelp/python-gearman)
redis (sudo easy_install redis)
Need capistrano/ext/multistage installed (gem install capistrano-ext)

***  Somewhere on an accessible server ***
gearman
redis

Todo : command to check if env is ok (services installed, capistrano path and rights ok etc.)

INSTALL
*** Capistrano server side


Export your capistrano db like this:
mysqldump -u root -p webistrano_prod --no-create-info -c > /tmp/webistrano-data.sql

