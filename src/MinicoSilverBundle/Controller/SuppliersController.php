<?php

namespace MinicoSilverBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use MinicoSilverBundle\Entity\Suppliers;
use MinicoSilverBundle\Form\SuppliersType;

/**
 * Suppliers controller.
 *
 * @Route("/suppliers")
 */
class SuppliersController extends Controller
{

    /**
     * Lists all Suppliers entities.
     *
     * @Route("/", name="suppliers")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('MinicoSilverBundle:Suppliers')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Suppliers entity.
     *
     * @Route("/", name="suppliers_create")
     * @Method("POST")
     * @Template("MinicoSilverBundle:Suppliers:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Suppliers();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('suppliers_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
    * Creates a form to create a Suppliers entity.
    *
    * @param Suppliers $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(Suppliers $entity)
    {
        $form = $this->createForm(SuppliersType::class, $entity, array(
            'action' => $this->generateUrl('suppliers_create'),
            'method' => 'POST',
        ));

        $form->add('submit', SubmitType::class, array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Suppliers entity.
     *
     * @Route("/new", name="suppliers_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Suppliers();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Suppliers entity.
     *
     * @Route("/{id}", name="suppliers_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MinicoSilverBundle:Suppliers')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Suppliers entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Suppliers entity.
     *
     * @Route("/{id}/edit", name="suppliers_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MinicoSilverBundle:Suppliers')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Suppliers entity.');
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
    * Creates a form to edit a Suppliers entity.
    *
    * @param Suppliers $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Suppliers $entity)
    {
        $form = $this->createForm(SuppliersType::class, $entity, array(
            'action' => $this->generateUrl('suppliers_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', SubmitType::class, array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Suppliers entity.
     *
     * @Route("/{id}", name="suppliers_update")
     * @Method("PUT")
     * @Template("MinicoSilverBundle:Suppliers:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MinicoSilverBundle:Suppliers')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Suppliers entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('suppliers_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Suppliers entity.
     *
     * @Route("/{id}", name="suppliers_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('MinicoSilverBundle:Suppliers')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Suppliers entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('suppliers'));
    }

    /**
     * Creates a form to delete a Suppliers entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('suppliers_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', SubmitType::class, array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
