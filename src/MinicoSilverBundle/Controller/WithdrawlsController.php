<?php

namespace MinicoSilverBundle\Controller;

use MinicoSilverBundle\Service\TransferService;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use MinicoSilverBundle\Entity\Withdrawls;
use MinicoSilverBundle\Form\WithdrawlsType;

/**
 * Withdrawls controller.
 *
 * @Route("/withdrawls")
 */
class WithdrawlsController extends Controller
{

    /**
     * Lists all Withdrawls entities.
     *
     * @Route("/", name="withdrawls")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('MinicoSilverBundle:Withdrawls')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Withdrawls entity.
     *
     * @Route("/", name="withdrawls_create")
     * @Method("POST")
     * @Template("MinicoSilverBundle:Withdrawls:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Withdrawls();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        /** @var Withdrawls $withdrawal */
        $withdrawal = $form->getData();

        /** @var TransferService $transferService */
        $transferService = $this->get('minico.silver.transfer_service');
        $maxQty = $transferService
            ->getTransferMaxQty(
                $withdrawal->getStorage(),
                $withdrawal->getProductId()
            );

        $remaining = $maxQty - $withdrawal->getQuantity();

        if ($remaining < 0) {
            $form
                ->get('quantity')
                ->addError(
                    new FormError(
                        "Valoarea maxima transferabila din gestiunea '{$withdrawal->getStorage()->getName()}' este {$maxQty}"
                    )
                );
        }
        if ($form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('withdrawls_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
    * Creates a form to create a Withdrawls entity.
    *
    * @param Withdrawls $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(Withdrawls $entity)
    {
        $form = $this->createForm(WithdrawlsType::class, $entity, array(
            'action' => $this->generateUrl('withdrawls_create'),
            'method' => 'POST',
        ));

        $form->add('submit', SubmitType::class, array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Withdrawls entity.
     *
     * @Route("/new", name="withdrawls_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Withdrawls();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Withdrawls entity.
     *
     * @Route("/{id}", name="withdrawls_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MinicoSilverBundle:Withdrawls')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Withdrawls entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Withdrawls entity.
     *
     * @Route("/{id}/edit", name="withdrawls_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MinicoSilverBundle:Withdrawls')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Withdrawls entity.');
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
    * Creates a form to edit a Withdrawls entity.
    *
    * @param Withdrawls $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Withdrawls $entity)
    {
        $form = $this->createForm(WithdrawlsType::class, $entity, array(
            'action' => $this->generateUrl('withdrawls_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', SubmitType::class, array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Withdrawls entity.
     *
     * @Route("/{id}", name="withdrawls_update")
     * @Method("PUT")
     * @Template("MinicoSilverBundle:Withdrawls:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MinicoSilverBundle:Withdrawls')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Withdrawls entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        /** @var Withdrawls $withdrawal */
        $withdrawal = $editForm->getData();

        /** @var TransferService $transferService */
        $transferService = $this->get('minico.silver.transfer_service');
        $maxQty = $transferService
            ->getTransferMaxQty(
                $withdrawal->getStorage(),
                $withdrawal->getProductId()
            );

        $remaining = $maxQty;
        $maxQty += $withdrawal->getQuantity();

        if ($remaining < 0) {
            $editForm
                ->get('quantity')
                ->addError(
                    new FormError(
                        "Valoarea maxima transferabila din gestiunea '{$withdrawal->getStorage()->getName()}' este {$maxQty}"
                    )
                );
        }

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('withdrawls_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Withdrawls entity.
     *
     * @Route("/{id}", name="withdrawls_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('MinicoSilverBundle:Withdrawls')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Withdrawls entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('withdrawls'));
    }

    /**
     * Creates a form to delete a Withdrawls entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('withdrawls_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', SubmitType::class, array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
