<?php

namespace Wapistrano\CoreBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;

class MiscExtension extends \Twig_Extension
{
    protected $container;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Returns a list of global functions to add to the existing list.
     *
     * @return array An array of global functions
     */
    public function getFunctions()
    {
        return array(
            'wapi_render_loader' => new \Twig_Function_Method($this, 'renderLoader', array('is_safe' => array('html')))
        );
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'wapi_misc';
    }

    public function renderLoader($parameters = array(), $name = null)
    {
        if(isset($parameters["loader"])) {
            $loader = $parameters["loader"];
        } else {
            $loader = "";
        }

        switch($loader) {
            case "bgWhite":
                $loader = "ajax-loader-electro-bgwhite.gif";
                break;

            default:
                $loader = "ajax-loader-electro.gif";
        }

        return $this->container->get("templating")->render("WapistranoCoreBundle:Misc:loader.html.twig",
            array("loader" => $loader,"message" => $parameters["message"]));

    }

}
