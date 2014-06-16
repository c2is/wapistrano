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

*** MIGRATION FROM WEBISTRANO ***
Export your capistrano db like this:
mysqldump -u root -p webistrano_prod --no-create-info -c > /tmp/webistrano-data.sql
sed -i -e "s/\`recipe_id\`, \`stage_id\`/\`recipes_id\`, \`stages_id\`/g" /Users/andre/Downloads/webistrano-data.sql
mysql -h 127.0.0.1 -u root wapistrano < /Users/andre/Downloads/webistrano-data.sql-e


*** Exclusive admin Grants***
Add/Edit recipe
Add/Edit user
Add/Edit host


