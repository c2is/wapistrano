<?php
namespace Wapistrano\CoreBundle;

use \Doctrine\Common\DataFixtures\FixtureInterface,
    \Doctrine\Common\Persistence\ObjectManager;
use Wapistrano\CoreBundle\entity\Users;

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
        $user = new Users();
        $user->setLogin("admin");
        $user->setCryptedPassword("admin");
        $user->setAdmin(1);
        $manager->persist($user);
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