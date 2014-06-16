<?php

namespace Wapistrano\ProfileBundle\Security;



class WapistranoUserRights
{
    protected $securityContext;
    protected $em;

    public function __construct($securityContext, $em)
    {
        $this->securityContext = $securityContext;
        $this->em = $em;
    }

    public function isProjectGranted($projectId)
    {
        if( $this->securityContext->isGranted("ROLE_ADMIN") ) {
            return true;
        }
        $user= $this->securityContext->getToken()->getUser();
        $project = $this->em->getRepository('WapistranoCoreBundle:Projects')->findOneBy(array("id" =>$projectId));

        if($user->getProject()->contains($project)) {
            return true;
        } else {
            return false;
        }
    }

}