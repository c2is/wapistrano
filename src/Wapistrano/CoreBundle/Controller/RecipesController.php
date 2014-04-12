<?php

namespace Wapistrano\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Wapistrano\CoreBundle\Entity\recipes;
use Wapistrano\CoreBundle\Form\recipesTypeAdd;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/recipes")
 */
class RecipesController extends Controller
{
    private $sectionTitle;
    private $sectionAction;
    private $sectionUrl;

    public function getSectionTitle() {
        if (null == $this->sectionTitle) {
            $this->sectionTitle = 'recipes';
        }
        return $this->sectionTitle;
    }

    public function getSectionAction() {
        if (null == $this->sectionAction) {
            $this->sectionAction = $this->generateUrl('recipesAdd');
        }
        return $this->sectionAction;
    }

    public function getSectionUrl() {
        if (null == $this->sectionUrl) {
            $this->sectionUrl = $this->generateUrl('recipesList');
        }
        return $this->sectionUrl;
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

        return array('sectionTitle' =>  $this->getSectionTitle(), 'sectionAction' => $this->getSectionAction(),
            'sectionUrl' => $this->getSectionUrl(), 'title' => 'List', 'recipes'=> $recipes, "flashMessage" => $flashMessage);
    }

    /**
     * @Route("/{id}", requirements={"id" = "\d+"}, name="recipesHome")
     * @Template("WapistranoCoreBundle::recipes_list.html.twig")
     */
    public function indexAction(Request $request)
    {

        $em = $this->container->get('doctrine')->getManager();
        $recipes = $em->getRepository('WapistranoCoreBundle:Recipes')->findAll();

        $session = $request->getSession();
        $flashMessage = implode("\n", $session->getFlashBag()->get('notice', array()));
        $session->getFlashBag()->clear('notice');

        return array('sectionTitle' =>  $this->getSectionTitle(), 'sectionAction' => $this->getSectionAction(),
            'sectionUrl' => $this->getSectionUrl(), 'title' => 'List', 'recipes'=> $recipes, "flashMessage" => $flashMessage);
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
        return array('sectionTitle' =>  $this->getSectionTitle(), 'sectionAction' => $this->getSectionAction(), 'sectionUrl' => $this->getSectionUrl(), 'title' => 'Add', 'form' => $form->createView());
        // return $this->render('WapistranoCoreBundle:Default:index.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/{id}/edit", name="recipesEdit")
     * @Template("WapistranoCoreBundle:Form:recipes_update.html.twig")
     */
    public function updateAction(Request $request, $id)
    {

        $em = $this->container->get('doctrine')->getManager();
        $Recipe = $em->getRepository('WapistranoCoreBundle:recipes')->findOneBy(array("id" => $id));

        $RecipeType = new recipesTypeAdd();


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
            $session->getFlashBag()->add('notice', 'Recipe '.$Recipe->getName().' updated');

            return $this->redirect($this->generateUrl('recipesList'));
        }
        return array('sectionTitle' =>  $this->getSectionTitle(), 'sectionAction' => $this->getSectionAction(), 'sectionUrl' => $this->getSectionUrl(), 'title' => 'Add', 'form' => $form->createView());
        // return $this->render('WapistranoCoreBundle:Default:index.html.twig', array('form' => $form->createView()));
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
        $session->getFlashBag()->add('notice', 'Recipe '.$Recipe->getName().'deleted');
        return $this->redirect($this->generateUrl('recipesHome'));
    }
}
