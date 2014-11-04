TODO
In CoreBundle/Stage/Stage.php function deployStage, change job async to sync because of unset prompted vars which can be performed
before the deploy in case of two python workers are running
* 1.1
 * Added export/duplicate feature