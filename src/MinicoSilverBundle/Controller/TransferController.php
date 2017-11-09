<?php

namespace MinicoSilverBundle\Controller;

use MinicoSilverBundle\Entity\Storage;
use MinicoSilverBundle\Entity\TransferRepository;
use MinicoSilverBundle\Service\TransferService;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use MinicoSilverBundle\Entity\Transfer;
use MinicoSilverBundle\Form\TransferType;

/**
 * Transfer controller.
 *
 * @Route("/transfer")
 */
class TransferController extends Controller
{

    /**
     * Lists all Transfer entities.
     *
     * @Route("/", name="transfer")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        /** @var TransferRepository $transferRepo */
        $transferRepo = $em->getRepository('MinicoSilverBundle:Transfer');
        $entities = $transferRepo->getAllTransfers();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Transfer entity.
     *
     * @Route("/", name="transfer_create")
     * @Method("POST")
     * @Template("MinicoSilverBundle:Transfer:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Transfer();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        /** @var Transfer $transfer */
        $transfer = $form->getData();

        /** @var TransferService $transferService */
        $transferService = $this->get('minico.silver.transfer_service');

        $em = $this->getDoctrine()->getManager();
        /** @var TransferRepository $transferRepo */
        $transferRepo = $em->getRepository('MinicoSilverBundle:Transfer');
        $limit = 10;

        $maxQty = $transferService
            ->getTransferMaxQty(
                $transfer->getFromStorage(),
                $transfer->getProduct()
            );

        $remaining = $maxQty - $transfer->getQty();

        if ($remaining < 0) {
            $form->get('qty')->addError(new FormError("Valoarea maxima transferabila din gestiunea '{$transfer->getFromStorage()->getName()}' este {$maxQty}"));
        }

        /** @var Transfer[] $oldTransfer */
        $oldTransfer = $transferRepo->findBy(
            array(),
            array('id'=>'desc'),
            $limit
        );

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            if ($form->get('submitReturn')->isClicked()) {
                // probably redirect to the add page again

                $entity = new Transfer();
                $newForm = $this->createCreateForm($entity);
                $newForm->get('fromStorage')->setData($transfer->getFromStorage());
                $newForm->get('toStorage')->setData($transfer->getToStorage());

                /** @var Transfer[] $oldTransfer */
                $oldTransfer = $transferRepo->findBy(
                    array(),
                    array('id'=>'desc'),
                    $limit
                );

                return array(
                    'entity' => $entity,
                    'form'   => $newForm->createView(),
                    'oldTransfer' => $oldTransfer
                );
            }
            return $this->redirect($this->generateUrl('transfer_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'oldTransfer' => $oldTransfer
        );
    }

    /**
     * Creates a form to create a Transfer entity.
     *
     * @param Transfer $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Transfer $entity)
    {
        $form = $this->createForm(TransferType::class, $entity, array(
            'action' => $this->generateUrl('transfer_create'),
            'method' => 'POST',
        ));

        $form->add('submit', SubmitType::class, array('label' => 'Create'));
        $form->add('submitReturn', SubmitType::class, array('label' => 'Create & return'));

        return $form;
    }

    /**
     * Displays a form to create a new Transfer entity.
     *
     * @Route("/new", name="transfer_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Transfer();
        $form   = $this->createCreateForm($entity);
        $em = $this->getDoctrine()->getManager();
        /** @var TransferRepository $transferRepo */
        $transferRepo = $em->getRepository('MinicoSilverBundle:Transfer');
        $limit = 10;

        /** @var Transfer[] $oldTransfer */
        $oldTransfer = $transferRepo->findBy(
            array(), array('id'=>'desc'), $limit
        );

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'oldTransfer' => $oldTransfer
        );
    }

    /**
     * Finds and displays a Transfer entity.
     *
     * @Route("/{id}", name="transfer_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MinicoSilverBundle:Transfer')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Transfer entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Transfer entity.
     *
     * @Route("/{id}/edit", name="transfer_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Transfer $entity */
        $entity = $em->getRepository('MinicoSilverBundle:Transfer')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Transfer entity.');
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
    * Creates a form to edit a Transfer entity.
    *
    * @param Transfer $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Transfer $entity)
    {
        $form = $this->createForm(TransferType::class, $entity, array(
            'action' => $this->generateUrl('transfer_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', SubmitType::class, array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Transfer entity.
     *
     * @Route("/{id}", name="transfer_update")
     * @Method("PUT")
     * @Template("MinicoSilverBundle:Transfer:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MinicoSilverBundle:Transfer')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Transfer entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        /** @var Transfer $transfer */
        $transfer = $editForm->getData();

        /** @var TransferService $transferService */
        $transferService = $this->get('minico.silver.transfer_service');
        $maxQty = $transferService
            ->getTransferMaxQty(
                $transfer->getFromStorage(),
                $transfer->getProduct()
            );

        $remaining = $maxQty; //- $transfer->getQty();

        if ($remaining < 0) {
            $editForm->get('qty')->addError(new FormError("Valoarea maxima transferabila este {$maxQty}"));
        }

        if ($editForm->isValid()) {
            $em->flush();
            return $this->redirect($this->generateUrl('transfer_show', array('id' => $entity->getId())));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Transfer entity.
     *
     * @Route("/{id}", name="transfer_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('MinicoSilverBundle:Transfer')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Transfer entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('transfer'));
    }

    /**
     * Creates a form to delete a Transfer entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('transfer_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', SubmitType::class, array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
