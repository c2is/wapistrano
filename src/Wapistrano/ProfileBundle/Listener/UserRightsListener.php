<?php

namespace Wapistrano\ProfileBundle\Listener;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Wapistrano\ProfileBundle\Controller\UserRightsController;
use Wapistrano\ProfileBundle\Security\WapistranoUserRights;

class UserRightsListener
{

    protected $wapistranoUserRights;

    public function __construct(WapistranoUserRights $wapistranoUserRights)
    {
        $this->wapistranoUserRights = $wapistranoUserRights;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();

        /*
         * $controller peut être une classe ou une closure. Ce n'est pas
         * courant dans Symfony2 mais ça peut arriver.
         * Si c'est une classe, elle est au format array
         */
        if (!is_array($controller)) {
            return;
        }

        if ($controller[0] instanceof UserRightsController) {
            $request = $event->getRequest();

            $params = $request->attributes->get('_route_params');
            $projectId = null;
            foreach($params as $index=>$param) {
                switch($index) {
                    case "id":
                        $projectId = $param;
                        break;
                }
            }

          if(null != $projectId) {
                if(! $this->wapistranoUserRights->isProjectGranted($projectId)) {
                    throw new AccessDeniedHttpException('Access denied !');
                }

          }
        }
    }
}