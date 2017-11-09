<?php

namespace MinicoSilverBundle\Controller;

use MinicoSilverBundle\Service\CacheInvalidatorService;
use MinicoSilverBundle\Entity\StorageRepository;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use MinicoSilverBundle\Entity\Storage;
use MinicoSilverBundle\Form\StorageType;

/**
 * Storage controller.
 *
 * @Route("/storage")
 */
class StorageController extends Controller
{

    /**
     * Lists all Storage entities.
     *
     * @Route("/", name="storage")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        if ($_SERVER['REQUEST_METHOD'] === 'PURGE') {
            $cacheDriver = $em->getConfiguration()->getResultCacheImpl();
            $cacheDriver->delete('storages');
        }
        /** @var StorageRepository $entitiesRepo */
        $entitiesRepo = $em->getRepository('MinicoSilverBundle:Storage');
        $entities = $entitiesRepo->getAllStorages();

        $response = $this->render(
            'MinicoSilverBundle:Storage:index.html.twig',
            array(
                'entities' => $entities,
            )
        );

//        if ($_SERVER['REQUEST_METHOD'] === 'PURGE') {
//            $response->setSharedMaxAge(0);
//        } else {
            // cache for 3600 seconds
        $response->setSharedMaxAge(3600);
//        }

        // (optional) set a custom Cache-Control directive
        $response->headers->addCacheControlDirective('must-revalidate', true);

        return $response;
    }

    /**
     * Creates a new Storage entity.
     * @var Request $request
     * @Route("/", name="storage_create")
     * @Method("POST")
     * @Template("MinicoSilverBundle:Storage:new.html.twig")
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function createAction(Request $request)
    {
        $entity = new Storage();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            /** @var CacheInvalidatorService $cacheInvalidatorService */
            $cacheInvalidatorService = $this->container->get(CacheInvalidatorService::ID);
            $cacheInvalidatorService->invalidateRoute('storage');

            return $this->redirect($this->generateUrl('storage_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Storage entity.
     *
     * @param Storage $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Storage $entity)
    {
        $form = $this->createForm(StorageType::class, $entity, array(
            'action' => $this->generateUrl('storage_create'),
            'method' => 'POST',
        ));

        $form->add('submit', SubmitType::class, array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Storage entity.
     *
     * @Route("/new", name="storage_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Storage();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Storage entity.
     *
     * @Route("/{id}", name="storage_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MinicoSilverBundle:Storage')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Storage entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Storage entity.
     *
     * @Route("/{id}/edit", name="storage_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Storage $entity */
        $entity = $em->getRepository('MinicoSilverBundle:Storage')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Storage entity.');
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
    * Creates a form to edit a Storage entity.
    *
    * @param Storage $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Storage $entity)
    {
        $form = $this->createForm(StorageType::class, $entity, array(
            'action' => $this->generateUrl('storage_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', SubmitType::class, array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Storage entity.
     *
     * @Route("/{id}", name="storage_update")
     * @Method("PUT")
     * @Template("MinicoSilverBundle:Storage:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var Storage $entity */
        $entity = $em->getRepository('MinicoSilverBundle:Storage')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Storage entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('storage_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Storage entity.
     *
     * @Route("/{id}", name="storage_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('MinicoSilverBundle:Storage')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Storage entity.');
            }

            $em->remove($entity);

            /** @var CacheInvalidatorService $cacheInvalidatorService */
            $cacheInvalidatorService = $this->container->get(CacheInvalidatorService::ID);
            $cacheInvalidatorService->invalidateRoute('storage');

            $em->flush();
        }

        return $this->redirect($this->generateUrl('storage'));
    }

    /**
     * Creates a form to delete a Storage entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('storage_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', SubmitType::class, array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
