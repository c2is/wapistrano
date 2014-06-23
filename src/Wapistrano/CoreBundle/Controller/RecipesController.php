<?php

namespace Wapistrano\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Wapistrano\CoreBundle\Entity\Recipes;
use Wapistrano\CoreBundle\Form\RecipesTypeAdd;
use APY\BreadcrumbTrailBundle\Annotation\Breadcrumb;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/recipes")
 * @Breadcrumb("Recipes", routeName="recipesList")
 */
class RecipesController extends Controller
{
    private $sectionAction;

    public function getSectionAction() {
        if (null == $this->sectionAction) {
            $this->sectionAction = $this->generateUrl('recipesAdd');
        }

        return $this->sectionAction;
    }

    /**
     * @Route("/", name="recipesList")
     * @Template("WapistranoCoreBundle::recipes_list.html.twig")
     */
    public function listAction(Request $request)
    {

        $em = $this->container->get('doctrine')->getManager();
        $recipes = $em->getRepository('WapistranoCoreBundle:Recipes')->findAll();

        $session = $request->getSession();
        $flashMessage = implode("\n", $session->getFlashBag()->get('notice', array()));
        $session->getFlashBag()->clear('notice');

        return array('barTitle' =>  'Recipes list', 'sectionAction' => $this->getSectionAction(),  'recipes'=> $recipes, "flashMessage" => $flashMessage);
    }

    public function getUrlAction($action, $id = ""){

        if ("" == $id) {
            return new Response($this->generateUrl('recipes'.$action));
        } else {
            return new Response($this->generateUrl('recipes'.$action, array("id" => $id)));

        }

    }
    /**
     * @Route("/add", name="recipesAdd")
     * @Template("WapistranoCoreBundle:Form:recipes_create.html.twig")
     */
    public function addAction(Request $request)
    {
        $RecipeType = new recipesTypeAdd();
        $Recipe = new recipes();


        $form = $this->get('form.factory')->create($RecipeType, $Recipe);
        $form->add('saveTop', 'submit');
        $form->add('saveBottom', 'submit');

        $form->handleRequest($request);

        if ($form->isValid()) {
            $today = new \DateTime();
            $Recipe->setCreatedAt($today);
            $Recipe = $form->getData();

            $manager = $this->getDoctrine()->getManager();
            $manager->persist($Recipe);
            $manager->flush();

            $session = $request->getSession();
            $session->getFlashBag()->add('notice', 'Recipe '.$Recipe->getName().' added');

            return $this->redirect($this->generateUrl('recipesList'));
        }
        return array('barTitle' =>  'Add new recipe', 'sectionAction' => $this->getSectionAction(), 'form' => $form->createView());
        // return $this->render('WapistranoCoreBundle:Default:index.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/{id}/edit", name="recipesEdit")
     * @Breadcrumb("{recipe.name}", routeName="recipesEdit", routeParameters={"id"="{id}"})
     * @Template("WapistranoCoreBundle:Form:recipes_update.html.twig")
     */
    public function updateAction(Request $request, Recipes $recipe)
    {
        $RecipeType = new recipesTypeAdd();

        $form = $this->get('form.factory')->create($RecipeType, $recipe);
        $form->add('saveTop', 'submit');
        $form->add('saveBottom', 'submit');

        $form->handleRequest($request);

        if ($form->isValid()) {
            $today = new \DateTime();
            $recipe->setCreatedAt($today);
            $recipe = $form->getData();

            $manager = $this->getDoctrine()->getManager();
            $manager->persist($recipe);
            $manager->flush();

            $session = $request->getSession();
            $session->getFlashBag()->add('notice', 'Recipe '.$recipe->getName().' updated');

            return $this->redirect($this->generateUrl('recipesList'));
        }
        return array('barTitle' =>  $recipe->getName(), 'sectionAction' => $this->getSectionAction(), 'form' => $form->createView());
    }

    /**
     * @Route("/{id}/delete", name="recipesDelete")
     */
    public function deleteAction(Request $request, $id)
    {
        $em = $this->container->get('doctrine')->getManager();
        $Recipe = $em->getRepository('WapistranoCoreBundle:recipes')->findOneBy(array("id" => $id));
        $em->remove($Recipe);
        $em->flush();

        $session = $request->getSession();
        $session->getFlashBag()->add('notice', 'Recipe '.$Recipe->getName().' deleted');
        return $this->redirect($this->generateUrl('recipesList'));
    }
}
