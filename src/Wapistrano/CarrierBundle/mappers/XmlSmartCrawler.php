<?php
/**
 * This file is part of the C2iS <http://wwww.c2is.fr/> wapistrano project.
 * André Cianfarani <a.cianfarani@c2is.fr>
 */

namespace Wapistrano\CarrierBundle\mappers;

class XmlSmartCrawler
{
    protected $serializer;
    protected $xmlCrawler;
    protected $entitiesNameSpace = "Wapistrano\\CoreBundle\\Entity\\";

    public function __construct($xmlString, $serializer)
    {
        $this->serializer = $serializer;
        $this->xmlCrawler = simplexml_load_string($xmlString);
    }

    public function getProject()
    {
        return $this->getItem("//project", "Projects");
    }

    public function getProjectConfigurations()
    {
        return $this->getList("//project/configuration_parameters/configuration", "ConfigurationParameters");
    }

    public function getStages()
    {
        return $this->getList("//project/stages/stage", "Stages");
    }

    public function getStageConfigurations($stageName)
    {
        return $this->getList("//project/stages/stage[name='".$stageName."']/configuration_parameters/configuration", "ConfigurationParameters");
    }

    public function getStageRecipes($stageName)
    {
        return $this->getList("//project/stages/stage[name='".$stageName."']/recipes/recipe", "Recipes");
    }

    public function getStageRoles($stageName)
    {
        $roles = $this->getList("//project/stages/stage[name='".$stageName."']/roles/role", "Roles");
        // unmap host
        $filteredRoles = array();
        foreach ($roles as $role) {
            $filteredRoles = $role->setHost(null);
        }

        return $filteredRoles;
    }

    public function getStageRoleHost($stageName, $roleName)
    {
        return $this->getItem("//project/stages/stage[name='".$stageName."']/roles/role[name='".$roleName."']/host", "Hosts");
    }

    public function getDefinition()
    {
        return <<<"XML"
<?xml version="1.0"?>
<project>
    <name></name>
    <description></description>
    <configuration_parameters>
        <configuration>
            <name></name>
            <value></value>
            <type>ProjectConfiguration</type>
            <prompt_on_deploy></prompt_on_deploy>
        </configuration>
    </configuration_parameters>
    <stages>
        <stage>
            <name></name>
            <alert_emails></alert_emails>
            <configuration_parameters>
                <configuration>
                    <name></name>
                    <value></value>
                    <type>StageConfiguration</type>
                    <prompt_on_deploy></prompt_on_deploy>
                </configuration>
            </configuration_parameters>
            <recipes>
                <recipe>
                    <name></name>
                    <description></description>
                    <body></body>
                </recipe>
            </recipes>
            <hosts>
                <host>
                    <name></name>
                    <alias></alias>
                    <description></description>
                </host>
            </hosts>
        </stage>
    </stages>
</project>
XML;

    }

    public function getItem($xpath, $entityName)
    {
        return $this->deserialize($this->xmlCrawler->xpath($xpath)[0]->saveXML(), $this->entitiesNameSpace.$entityName);
    }

    protected function getList($xpath, $entityName)
    {
        $configurations = $this->xmlCrawler->xpath($xpath);

        $configurationsEntities = array();
        foreach ($configurations as $configuration) {
            $configurationsEntities[] = $this->deserialize($configuration->saveXML(), $this->entitiesNameSpace.$entityName);
        }

        return $configurationsEntities;
    }

    protected function deserialize($xmlString, $entityFullPath)
    {
        return $this->serializer->deserialize($xmlString, $entityFullPath, "xml");
    }

} 