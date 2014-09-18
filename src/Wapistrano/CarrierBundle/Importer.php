<?php
/**
 * This file is part of the C2iS <http://wwww.c2is.fr/> wapistrano project.
 * André Cianfarani <a.cianfarani@c2is.fr>
 */
namespace Wapistrano\CarrierBundle;

use Symfony\Component\DomCrawler\Crawler;
use Wapistrano\CoreBundle\Entity\Projects;

use Wapistrano\CarrierBundle\mappers\XmlSmartCrawler;
use Wapistrano\CarrierBundle\mappers\DbPercolator;


class Importer {

    private $em;

    function __construct($em)
    {
        $this->em = $em;
    }

    function import($filePath, $serializer)
    {
        try {
            $xml = file_get_contents($filePath);
        } catch (\Exception $e) {
            throw $e;
        }

        $smartCrawler = new XmlSmartCrawler($xml, $serializer);
        $percolator = new DbPercolator($this->em);
        $project = $smartCrawler->getProject();



        $percolator->save($project, array("name"));


        // var_dump($project);
        foreach ($smartCrawler->getProjectConfigurations() as $configuration) {
            $configuration->setProjectId($project->getId());
            $percolator->save($configuration);
        }

        foreach ($smartCrawler->getStages() as $stage) {
            $stage->setProject($project);

            $percolator->save($stage);

            // configurations are binded to project, so we create all
            foreach ($smartCrawler->getStageConfigurations($stage->getName()) as $configuration) {
                $configuration->setProjectId($project->getId());
                $configuration->setStageId($stage->getId());

                $percolator->save($configuration);
            }

            // recipes are shared by projects, so we add constraint to manage creation or not
            foreach ($smartCrawler->getStageRecipes($stage->getName()) as $recipe) {
                if (null == $existingRecipe = $percolator->get($recipe, array("name"))) {
                    $percolator->save($recipe);
                    $stage->addRecipe($recipe);
                } else {
                    $stage->addRecipe($existingRecipe);
                }
            }

            foreach ($smartCrawler->getStageRoles($stage->getName()) as $role) {
                $host = $smartCrawler->getStageRoleHost($stage->getName(), $role->getName());
                $role->setStage($stage);

                if (null == $existingHost = $percolator->get($host, array("name"))) {
                    $percolator->save($host);
                    $role->setHost($host);
                } else {
                    $role->setHost($existingHost);
                }

                $percolator->save($role);
            }

            $percolator->save($stage);

        }

    }

}