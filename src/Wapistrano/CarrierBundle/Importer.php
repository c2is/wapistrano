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
        } catch(\Exception $e) {
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

                $percolator->save($recipe, array("name", "description", "body"));

                $stage->addRecipe($recipe);
            }

            $percolator->save($stage);

        }

        die();
        // $stage->setProject($project); $stage->addRecipe($recipe);
        $stages = $smartCrawler->getStages();
        // need projectId end stageId
        $stageConfigurations = $smartCrawler->getStageConfigurations("preprod");

        $smartCrawler->getStageRoles("prod");

        $smartCrawler->getStageRoleHost("prod", "Web");

        $stageRecipes = $smartCrawler->getStageRecipes("preprod");

        var_dump($stageHosts);

    }

}