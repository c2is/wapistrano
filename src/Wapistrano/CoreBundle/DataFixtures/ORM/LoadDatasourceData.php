<?php
namespace Wapistrano\CoreBundle;

use \Doctrine\Common\DataFixtures\FixtureInterface,
    \Doctrine\Common\Persistence\ObjectManager;
use Wapistrano\CoreBundle\entity\Users;
use Wapistrano\CoreBundle\entity\Projects;
use Wapistrano\CoreBundle\entity\Hosts;
use Wapistrano\CoreBundle\entity\Recipes;
use Wapistrano\CoreBundle\entity\Stages;

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

        // Users
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

        // Project
        $project = new Projects();
        $project->setName("Project One");
        $project->setDescription("Project One Description");
        $project->setCreatedAt($today);
        $manager->persist($project);
        $manager->flush();

        $project = new Projects();
        $project->setName("Project Two");
        $project->setDescription("Project Two Description");
        $project->setCreatedAt($today);
        $manager->persist($project);
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