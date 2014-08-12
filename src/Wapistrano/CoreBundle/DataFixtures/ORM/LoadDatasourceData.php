<?php
namespace Wapistrano\CoreBundle;

use \Doctrine\Common\DataFixtures\FixtureInterface,
    \Doctrine\Common\Persistence\ObjectManager;
use Wapistrano\CoreBundle\entity\Users;
use Wapistrano\CoreBundle\entity\Projects;
use Wapistrano\CoreBundle\entity\Hosts;
use Wapistrano\CoreBundle\entity\Recipes;
use Wapistrano\CoreBundle\entity\Stages;
use Wapistrano\CoreBundle\entity\Roles;
use Wapistrano\CoreBundle\entity\ConfigurationParameters;

/**
 * Class LoadDatasourceData
 *
 * Load data fixture for ArtsysBundle
 *
 * @package Seh\Bundle\ArtsysBundle
 */
class LoadDatasourceData implements FixtureInterface
{
    /**
     * Définie les entités à créer dans la base pour le bon fonctionnement du ArtsysBundle
     * 
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $today = new \DateTime();

        /************************************
         *              USERS                *
         ************************************/
        $user = new Users();
        $user->setLogin("admin");
        $user->setCryptedPassword("admin");
        $user->setAdmin(1);
        $manager->persist($user);
        $manager->flush();

        $user = new Users();
        $user->setLogin("dev");
        $user->setCryptedPassword("dev");
        $user->setAdmin(0);
        $manager->persist($user);
        $manager->flush();

        /************************************
         *              PROJECT             *
         ************************************/
        $project = new Projects();
        $project->setName("Project Test");
        $project->setDescription("This is a fake project as an example of a Symfony's like project deployment");
        $project->setCreatedAt($today);
        $manager->persist($project);
        $manager->flush();

        /************************************
         *              HOST                 *
         ************************************/
        $host = new Hosts();
        $host->setName("10.0.1.15");
        $host->setAlias("hostfordemo.local");
        $host->setDescription("This is a fake host, just for example");
        $host->setCreatedAt($today);
        $manager->persist($host);
        $manager->flush();

        /************************************
         *              RECIPES             *
         ************************************/
        $recipes = array();

        $recipe = new Recipes();
        $recipe->setName("Setup");
        $recipe->setDescription("Build directories 's arch when setup task is launched");
        $recipe->setBody(
<<<EOT
task :setup, :except => { :no_release => true } do
    dirs = uploads_dirs.map { |d| File.join(shared_path, d) }
    run "#{try_sudo} mkdir -p #{dirs.join(' ')} && #{try_sudo} chmod g+w #{deploy_to}/*"
end
after "deploy:symlink", "deploy:cleanup"
EOT
        );
        $recipe->setCreatedAt($today);
        $manager->persist($recipe);
        $manager->flush();
        $recipes[] = $recipe;

        // Recipe
        $recipe = new Recipes();
        $recipe->setName("Register dirs");
        $recipe->setDescription("Register dirs which are to be placed in shared area");
        $recipe->setBody(
            <<<EOT
task :register_dirs do
   set :uploads_dirs,    %w(web/uploads vendor)
   set :shared_children, fetch(:shared_children) + fetch(:uploads_dirs)
end
on :start,  "register_dirs"
EOT
        );
        $recipe->setCreatedAt($today);
        $manager->persist($recipe);
        $manager->flush();
        $recipes[] = $recipe;

        // Recipe
        $recipe = new Recipes();
        $recipe->setName("Symlinks");
        $recipe->setDescription("Create symlinks");
        $recipe->setBody(
            <<<EOT
namespace :uploads do
task :symlink, :except => { :no_release => true } do
  on_rollback do
     run "cd #{previous_release}; umask 002; #{composer} install -n"
    end
    run "if [ -d '#{release_path}/app/cache' ]; then rm -rf #{release_path}/app/cache; fi; umask 000; mkdir #{release_path}/app/cache;"
    run "rm -rf #{release_path}/app/logs"
    run "ln -nfs #{shared_path}/log #{release_path}/app/logs"

    run "rm -rf #{release_path}/web/uploads"
    run "ln -nfs #{shared_path}/web/uploads #{release_path}/web/uploads"
    run "rm -rf #{release_path}/web/media"
    run "if [ -d #{shared_path}/web/media ]; then ln -nfs #{shared_path}/web/media #{release_path}/web/media; fi"

    run "rm -rf #{release_path}/vendor"
    if exists?(:vendor_path)
      #if !vendor_path.empty?
        run "mkdir -p #{vendor_path}"
        run "ln -nfs #{vendor_path} #{release_path}/vendor"
      #else
      #  run "ln -nfs #{shared_path}/vendor #{release_path}/vendor"
      #end
    else
        run "ln -nfs #{shared_path}/vendor #{release_path}/vendor"
    end
end
end
EOT
        );
        $recipe->setCreatedAt($today);
        $manager->persist($recipe);
        $manager->flush();
        $recipes[] = $recipe;

        /************************************
         *              STAGE               *
         ************************************/
        $stage = new Stages();
        $stage->setName("preprod");
        $stage->setProject($project);
        $stage->setCreatedAt($today);
        foreach($recipes as $recipe) {
            $stage->addRecipe($recipe);
        }
        $manager->persist($stage);
        $manager->flush();

        /************************************
         *              ROLE                 *
         ************************************/
        $role = new Roles();
        $role->setName("Web");
        $role->setStage($stage);
        $role->setHost($host);
        $manager->persist($role);
        $manager->flush();

        /*******************************************
         * CONFIGURATIONPARAMETERS : PROJECT LEVEL *
         ******************************************/
        // ConfigurationParameters : project level
        $conf = new ConfigurationParameters();
        $conf->setName("repository");
        $conf->setValue("git@github.com:c2is/Walker.git");
        $conf->setType("ProjectConfiguration");
        $conf->setProjectId($project->getId());
        $manager->persist($conf);
        $manager->flush();

        $conf = new ConfigurationParameters();
        $conf->setName("deploy_via");
        $conf->setValue(":copy");
        $conf->setType("ProjectConfiguration");
        $conf->setProjectId($project->getId());
        $manager->persist($conf);
        $manager->flush();

        $conf = new ConfigurationParameters();
        $conf->setName("git_shallow_clone");
        $conf->setValue("1");
        $conf->setType("ProjectConfiguration");
        $conf->setProjectId($project->getId());
        $manager->persist($conf);
        $manager->flush();

        $conf = new ConfigurationParameters();
        $conf->setName("scm");
        $conf->setValue("git");
        $conf->setType("ProjectConfiguration");
        $conf->setProjectId($project->getId());
        $manager->persist($conf);
        $manager->flush();

        $conf = new ConfigurationParameters();
        $conf->setName("use_sudo");
        $conf->setValue("false");
        $conf->setType("ProjectConfiguration");
        $conf->setProjectId($project->getId());
        $manager->persist($conf);
        $manager->flush();

        $conf = new ConfigurationParameters();
        $conf->setName("composer");
        $conf->setValue("/var/www/composer.phar");
        $conf->setType("ProjectConfiguration");
        $conf->setProjectId($project->getId());
        $manager->persist($conf);
        $manager->flush();

        /*******************************************
         * CONFIGURATIONPARAMETERS : STAGE LEVEL *
         ******************************************/
        $conf = new ConfigurationParameters();
        $conf->setName("user");
        $conf->setValue("ssh-user");
        $conf->setType("StageConfiguration");
        $conf->setProjectId($project->getId());
        $conf->setStageId($stage->getId());
        $manager->persist($conf);
        $manager->flush();

        $conf = new ConfigurationParameters();
        $conf->setName("password");
        $conf->setValue("");
        $conf->setType("StageConfiguration");
        $conf->setPromptOnDeploy(true);
        $conf->setProjectId($project->getId());
        $conf->setStageId($stage->getId());
        $manager->persist($conf);
        $manager->flush();

    }

    /**
     * Retourne le numéro d'ordre de chargement des fixtures pour ArtsysBundle
     *
     * @return int
     */
    public function getOrder()
    {
        return 1;
    }
}