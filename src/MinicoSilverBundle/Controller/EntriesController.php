<?php

namespace MinicoSilverBundle\Controller;

use MinicoSilverBundle\Entity\Category;
use MinicoSilverBundle\Entity\CategoryRepository;
use MinicoSilverBundle\Entity\Products;
use MinicoSilverBundle\Entity\Suppliers;
use MinicoSilverBundle\Entity\SuppliersRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use MinicoSilverBundle\Entity\Entries;
use MinicoSilverBundle\Form\EntriesType;
use Symfony\Component\HttpFoundation\Response;

/**
 * Entries controller.
 *
 * @Route("/entries")
 */
class EntriesController extends Controller
{

    /**
     * Lists all Entries entities.
     *
     * @Route("/", name="entries")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('MinicoSilverBundle:Entries')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Entries entity.
     *
     * @Route("/", name="entries_create")
     * @Method("POST")
     * @Template("MinicoSilverBundle:Entries:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Entries();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('entries_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
    * Creates a form to create a Entries entity.
    *
    * @param Entries $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(Entries $entity)
    {
        $em = $this->container->get('doctrine')->getManager();
        $form = $this->createForm(EntriesType::class, $entity, array(
            'action' => $this->generateUrl('entries_create'),
            'method' => 'POST',
            'entity_manager' => $em,
        ));

        return $form;
    }

    /**
     * Displays a form to create a new Entries entity.
     *
     * @Route("/new", name="entries_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Entries();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Entries entity.
     *
     * @Route("/{id}", name="entries_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MinicoSilverBundle:Entries')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Entries entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Entries entity.
     *
     * @Route("/{id}/edit", name="entries_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MinicoSilverBundle:Entries')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Entries entity.');
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
    * Creates a form to edit a Entries entity.
    *
    * @param Entries $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Entries $entity)
    {
        $em = $this->container->get('doctrine')->getManager();
        $form = $this->createForm(new EntriesType($em), $entity, array(
            'action' => $this->generateUrl('entries_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', SubmitType::class, array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Entries entity.
     *
     * @Route("/{id}", name="entries_update")
     * @Method("PUT")
     * @Template("MinicoSilverBundle:Entries:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MinicoSilverBundle:Entries')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Entries entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('entries_show', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Entries entity.
     *
     * @Route("/{id}", name="entries_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('MinicoSilverBundle:Entries')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Entries entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('entries'));
    }

    /**
     * Creates a form to delete a Entries entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('entries_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', SubmitType::class, array('label' => 'Delete'))
            ->getForm()
        ;
    }


    public function addNewEntriesAction()
    {
        $em = $this->getDoctrine()->getManager();
        /** @var CategoryRepository $categoryRepository */
        $categoryRepository = $em->getRepository('MinicoSilverBundle:Category');

        /** @var Category[] $categories */
        $categories = $categoryRepository->findAll();

        /** @var SuppliersRepository $supplierRepository */
        $supplierRepository = $em->getRepository('MinicoSilverBundle:Suppliers');

        /** @var Suppliers[] $categories */
        $suppliers = $supplierRepository->findAll();

        $margins = array();

        for ($i=5; $i<=150; $i+=5) {
            $margins[] = $i;
        }
        return $this->render(
            'MinicoSilverBundle:Entries:newentriesandproducts.html.twig',
            array(
                'categories' => $categories,
                'margins'    => $margins,
                'suppliers'  => $suppliers,
            )
        );
    }

    /**
     * @param Request $request
     */
    public function saveNewEntriesAction(Request $request)
    {
        //todo: verificare daca mai este in tabela - dupa cod, pret intrare, pret vanzare
        $em = $this->getDoctrine()->getEntityManager();

        try {
            $em->getConnection()->beginTransaction();
            $message = array();
            if (!$request->isMethod('POST')) {
                $message['error'] = 'Metoda nu este POST!!!';
            }

            $productCode    = $request->get('productCode');
            $salePrice      = $request->get('salePrice');
            $supplierId     = $request->get('supplierId');
            $entryPrice     = $request->get('entryPrice');
            $description    = $request->get('description');
            $categoryId     = $request->get('categoryId');
            $quantity       = $request->get('quantity');
            $date           = new \DateTime($request->get('date'));//'2000-01-01'

            $category = $em->getRepository('MinicoSilverBundle:Category')->findOneById($categoryId);
            $supplier = $em->getRepository('MinicoSilverBundle:Suppliers')->findOneById($supplierId);

            $filename = $productCode.date('YmdHis') . '.jpg';

            copy('my_file.jpg','./images/'.$filename);

            /** @var Products $product */
            $product = new Products();

            $product
                ->setCategory($category)
                ->setEntryPrice($entryPrice)
                ->setProductCode($productCode)
                ->setProductDescription($description)
                ->setSupplier($supplier)
                ->setSalePrice($salePrice)
                ->setPhoto($filename);

            /** @var Entries $entries */
            $entries = new Entries();

            $entries
                ->setDate($date)
                ->setProductId($product)
                ->setQuantity($quantity);

            $em->persist($product);
            $em->persist($entries);

//            die('OK');
            $em->flush();
            $em->getConnection()->commit();
            if (array_key_exists('error', $message)) {
                return new Response(json_encode($message));
            } else {
                $message['succes'] = 'Success';
                return new Response(json_encode($message));
            }
        } catch(Exception $e) {
            $em->getConnection()->rollback();
            throw $e;
        }
    }


    public function photoTestAction()
    {

        $route = $this->container->get('router')->generate('image_path');

        $jpeg_data = file_get_contents('php://input');
        $filename = "my_file.jpg";
        $result = file_put_contents( $filename, $jpeg_data );

//        $filename = date('YmdHis') . '.jpg';

//        echo "$filename";
//        $result = file_put_contents( 'images/'.$filename, file_get_contents('php://input') );
//        try {
////            $result = file_put_contents( 'images/'.$filename, file_get_contents($route) );
//            $result = file_put_contents( $filename, file_get_contents($route) );
//        } catch (Exception $e) {
//            print_r($e); die;
//        }
        if (!$result) {
            print "ERROR: Failed to write data to $filename, check permissions\n";
            exit();
        }

        $url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/' . $filename;
        print "$url\n";
    }
}
