<?php

namespace MinicoSilverBundle\Controller;

use MinicoSilverBundle\Entity\Entries;
use MinicoSilverBundle\Entity\Transfer;
use MinicoSilverBundle\Form\EntriesType;
use MinicoSilverBundle\Form\ProductAndOffsetType;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Simple\FilesystemCache;
use Symfony\Component\Cache\Simple\RedisCache;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use MinicoSilverBundle\Entity\Products;
use MinicoSilverBundle\Form\ProductsType;

/**
 * Products controller.
 *
 * @Route("/products")
 */
class ProductsController extends Controller
{

    /**
     * Lists all Products entities.
     *
     * @Route("/", name="products")
     * @Template("MinicoSilverBundle:Products:index.html.twig")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $filter = array();
        $offset = 0;
        $form = $this->createForm(
            ProductAndOffsetType::class,
            null,
            array(
                'action' => $this->generateUrl('products'),
                'method' => 'POST'
            )
        );

        //$client = RedisAdapter::createConnection(
        //    'redis://vanilla2365-centos7.om2.c.emag.network:6379'
        //);
        //
        //$cacheName = 'products_list';
        //
        //if ($client->get($cacheName)) {
        //    $a = $client->get($cacheName);
        //}
        //
        //$client->set($cacheName, json_encode([1,2,2,4]));
        //$client->save();

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $formData = $form->getData();
            if (!empty($formData["code"])) {
                $filter = array(
                    'productCode' => $formData["code"]
                );
            }

            if (!empty($formData["offset"])) {
                $offset = $formData["offset"];
            }
        } else {
            $filter = array();
            $offset = 0;
        }

        $entities = $em->getRepository('MinicoSilverBundle:Products')->findBy(
            $filter,
            array(),
            100,
            $offset
        );

        $response = $this->render(
            'MinicoSilverBundle:Products:index.html.twig',
            array(
                'entities' => $entities,
                'form' => $form->createView(),
            )
        );

        return $response;
    }
    /**
     * Creates a new Products entity.
     *
     * @Route("/", name="products_create")
     * @Method("POST")
     * @Template("MinicoSilverBundle:Products:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Products();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('products_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
    * Creates a form to create a Products entity.
    *
    * @param Products $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(Products $entity)
    {
        $form = $this->createForm(ProductsType::class, $entity, array(
            'action' => $this->generateUrl('products_create'),
            'method' => 'POST',
        ));

        $form->add('submit', SubmitType::class, array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Products entity.
     *
     * @Route("/new", name="products_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Products();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Products entity.
     *
     * @Route("/{id}", name="products_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Products $entity */
        $entity = $em->getRepository('MinicoSilverBundle:Products')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Products entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $entries_form = $this
            ->createForm(
                new EntriesType($em, $entity->getId()),
                null,
                array(
                    'action' => $this->generateUrl('entries_create')

                )
            );

        $entriesShow = false;
        /** @var Entries[] $entries */
        $entries = $entity->getEntries();
        if (count($entries)) {
            $entriesShow = true;
        }
        $transfersShow = false;
        /** @var Transfer[] $transfers */
        $transfers = $entity->getTransfers();
        if (count($transfers)) {
            $transfersShow = true;
        }

        return array(
            'entity'      => $entity,
            'entries_form' => $entries_form->createView(),
            'delete_form' => $deleteForm->createView(),
            'entriesShow' => $entriesShow,
            'transfersShow' => $transfersShow,
            'entries' => $entries,
            'transfers' => $transfers,
        );
    }

    /**
     * Displays a form to edit an existing Products entity.
     *
     * @Route("/{id}/edit", name="products_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MinicoSilverBundle:Products')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Products entity.');
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
    * Creates a form to edit a Products entity.
    *
    * @param Products $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Products $entity)
    {
        $form = $this->createForm(ProductsType::class, $entity, array(
            'action' => $this->generateUrl('products_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', SubmitType::class, array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Products entity.
     *
     * @Route("/{id}", name="products_update")
     * @Method("PUT")
     * @Template("MinicoSilverBundle:Products:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MinicoSilverBundle:Products')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Products entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('products_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Products entity.
     *
     * @Route("/{id}", name="products_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('MinicoSilverBundle:Products')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Products entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('products'));
    }

    /**
     * Creates a form to delete a Products entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('products_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', SubmitType::class, array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
