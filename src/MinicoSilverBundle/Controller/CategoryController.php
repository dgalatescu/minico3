<?php

namespace MinicoSilverBundle\Controller;

use MinicoSilverBundle\Entity\CategoryRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use MinicoSilverBundle\Entity\Category;
use MinicoSilverBundle\Form\CategoryType;

/**
 * Category controller.
 *
 * @Route("/category")
 */
class CategoryController extends Controller
{

    /**
     * Lists all Category entities.
     *
     * @Route("/", name="category")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var CategoryRepository $categoryRepo */
        $categoryRepo = $em->getRepository('MinicoSilverBundle:Category');
        $entities = $categoryRepo->getAllCategories();
        $response = $this->render(
            'MinicoSilverBundle:Category:index.html.twig',
            array(
                'entities' => $entities,
            )
        );

        // cache for 3600 seconds
//        $response->setSharedMaxAge(3600);

        // (optional) set a custom Cache-Control directive
//        $response->headers->addCacheControlDirective('must-revalidate', true);

        return $response;
    }

    /**
     * @Route("/ajax_titlte", name="ajax_title")
     */
    public function ajaxTitleAction()
    {
        $request = $this->getRequest();
        if ($request->isXmlHttpRequest()) {
            $value = $request->get('term');
            $em = $this->getDoctrine()->getEntityManager();
            $posts = $em->getRepository('MinicoSilverBundle:Category')->findAjaxValue($value);
            $json = array();
            foreach ($posts as $post) {
                $json[] = array(
                    'label' => $post->getTitle(), 'value' => $post->getTitle()
                );
            }
            $response = new Response();
            $response->setContent(json_encode($json));

            return $response;
        } else {
            return new Response('Forbidden', 403);
        }
    }


    public function listAction()
    {
        /** @var $request Request */
        $request = $this->get('request');

//        /** @var OrderStatusReasonService $orderStatusReasonService */
//        $orderStatusReasonService = $this->get('eis.order.order_status_reason');
//
//        $reversalOrderStatusReasons = $orderStatusReasonService
//            ->getGridInformation($request->get('sidx', 'id'), $request->get('sord', 'ASC'));

        $em = $this->getDoctrine()->getManager();
        /** @var Category $categories */
        $categories = $em
            ->getRepository('MinicoSilverBundle:Category')
            ->findAll();

        /** @var $paginator Paginator */
        $paginator = $this->get('knp_paginator');

        /** @var $pagination SlidingPagination */
        $pagination = $paginator->paginate(
            $categories,
            $request->get('page', 1) /*page number*/,
            $request->get('rows', 10) /*limit per page*/
        );

        $result = array();
        $result['page'] = $request->get('page', 1);
        $result['rows'] = $pagination->getItems();
        $result['records'] = $pagination->getTotalItemCount();
        $result['total'] = ceil($pagination->getTotalItemCount() / $request->get('rows', 10));
//
        return new JsonResponse($result);
    }

    /**
     * Creates a new Category entity.
     *
     * @Route("/", name="category_create")
     * @Method("POST")
     * @Template("MinicoSilverBundle:Category:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Category();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('category_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
    * Creates a form to create a Category entity.
    *
    * @param Category $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(Category $entity)
    {
        $form = $this->createForm(CategoryType::class, $entity, array(
            'action' => $this->generateUrl('category_create'),
            'method' => 'POST',
        ));

        $form->add('submit', SubmitType::class, array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Category entity.
     *
     * @Route("/new", name="category_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Category();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Category entity.
     *
     * @Route("/{id}", name="category_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MinicoSilverBundle:Category')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Category entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Category entity.
     *
     * @Route("/{id}/edit", name="category_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MinicoSilverBundle:Category')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Category entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
    * Creates a form to edit a Category entity.
    *
    * @param Category $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Category $entity)
    {
        $form = $this->createForm(CategoryType::class, $entity, array(
            'action' => $this->generateUrl('category_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', SubmitType::class, array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Category entity.
     *
     * @Route("/{id}", name="category_update")
     * @Method("PUT")
     * @Template("MinicoSilverBundle:Category:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MinicoSilverBundle:Category')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Category entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('category_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Category entity.
     *
     * @Route("/{id}", name="category_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('MinicoSilverBundle:Category')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Category entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('category'));
    }

    /**
     * Creates a form to delete a Category entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('category_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', SubmitType::class, array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
