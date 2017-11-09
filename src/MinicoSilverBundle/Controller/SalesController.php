<?php

namespace MinicoSilverBundle\Controller;

use Doctrine\ORM\EntityManager;
use MinicoSilverBundle\Entity\Products;
use MinicoSilverBundle\Entity\SalesRepository;
use MinicoSilverBundle\Entity\Withdrawls;
use MinicoSilverBundle\Form\SalesEditType;
use MinicoSilverBundle\Service\StorageService;
use MinicoSilverBundle\Service\TransferService;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use MinicoSilverBundle\Entity\Sales;
use MinicoSilverBundle\Entity\Entries;
use MinicoSilverBundle\Form\SalesType;
use MinicoSilverBundle\Service\ProductsService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sales controller.
 *
 * @Route("/sales")
 */
class SalesController extends Controller
{

    /**
     * Lists all Sales entities.
     *
     * @Route("/", name="sales")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var SalesRepository $salesRepo */
        $salesRepo = $em->getRepository('MinicoSilverBundle:Sales');

        /** @var Sales[] $sales */
        $sales = $salesRepo->findAll();
        $products = array();
        $saleStorages = array();

        /** @var Sales $sale */
        foreach ($sales as $sale) {
            if (!in_array($sale->getProductId(), $products)) {
                $products[] = $sale->getProductId();
            }
            if (!in_array($sale->getFromStorage(), $saleStorages)) {
                $saleStorages[] = $sale->getFromStorage();
            }
        }
        $defaultData = array('message' => 'Type here');

        $form = $this
            ->createFormBuilder($defaultData)
            ->add(
                'dateStart',
                'genemu_jquerydate',
                array(
                    'widget' => 'single_text',
                    'format' => 'yyyy-MM-dd'
                )
            )
            ->add(
                'dateEnd',
                'genemu_jquerydate',
                array(
                    'widget' => 'single_text',
                    'format' => 'yyyy-MM-dd',
                )
            )
            ->add(
                'storage',
                'entity',
                array(
                    'class' => 'MinicoSilverBundle:Storage',
                    'choices'     => $saleStorages,
                    'empty_value' => 'All',
                    'required' => false
                )
            )
            ->add(
                'productId',
                'entity',
                array(
                    'class' => 'MinicoSilverBundle:Products',
                    'choices'     => $products,
                    'empty_value' => 'Choose an option',
                    'required' => false
                )
            )
            ->add('submit','submit')
            ->getForm();

        if ($request->getMethod() == "POST") {
            $form->submit($request);
            if ($form->isValid()) {
                $postData = current($request->request->all());

                $startDate = $postData['dateStart'];
                $endDate = $postData['dateEnd'];
                $storage = $postData['storage'];
                $product = $postData['productId'];
                $entities = $salesRepo
                    ->getSalesByDates($startDate, $endDate, $storage, $product)
                    ->getQuery()
                    ->getResult();
            }
        } else {
            /** @var Sales[] $entities */
            $entities = $em
                ->getRepository('MinicoSilverBundle:Sales')
                ->findBy(
                    array(),
                    array(
                        'date' => 'DESC'
                    ),
                    100
                );
        }

        $supplierValues = array();
        $totalOwe = 0;
        $totalSale = 0;
        /** @var Sales $sale */
        foreach ($entities as $sale) {
            $supplierId = $sale->getProductId()->getSupplier()->getId();
            if (!array_key_exists($supplierId, $supplierValues)) {
                $supplierValues[$supplierId]['saleTotal'] = 0;
                $supplierValues[$supplierId]['oweTotal'] = 0;
                $supplierValues[$supplierId]['SupplierName'] = $sale->getProductId()->getSupplier()->getName();
            }

            $supplierValues[$supplierId]['saleTotal'] += $sale->getQuantity() * $sale->getProductId()->getSalePrice();
            $supplierValues[$supplierId]['oweTotal'] += $sale->getQuantity() * $sale->getProductId()->getEntryPrice();
            $totalOwe += $sale->getQuantity() * $sale->getProductId()->getEntryPrice();
            $totalSale += $sale->getQuantity() * $sale->getProductId()->getSalePrice();
        }

        $supplierValues[1000]['SupplierName'] = 'Total';
        $supplierValues[1000]['saleTotal'] = $totalSale;
        $supplierValues[1000]['oweTotal'] = $totalOwe;

        return array(
            'entities' => $entities,
            'supplierValues' => $supplierValues,
            'form' => $form->createView()
        );
    }
    /**
     * Creates a new Sales entity.
     *
     * @Route("/", name="sales_create")
     * @Method("POST")
     * @Template("MinicoSilverBundle:Sales:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Sales();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);
        $em = $this->getDoctrine()->getManager();

        /** @var Sales $sale */
        $sale = $form->getData();
        /** @var SalesRepository $saleRepo */
        $saleRepo = $em->getRepository('MinicoSilverBundle:Sales');
        $limit = 10;
        /** @var Sales[] $oldSales */
        $oldSales = $saleRepo->findBy(
            array(), array('id'=>'desc'), $limit
        );

        if ($sale->getProductId()) {
            /** @var TransferService $transferService */
            $transferService = $this->get('minico.silver.transfer_service');
            $maxQty = $transferService
                ->getTransferMaxQty(
                    $sale->getFromStorage(),
                    $sale->getProductId()
                );

            $remaining = $maxQty - $sale->getQuantity();

            if ($remaining < 0) {
                $form
                    ->get('quantity')
                    ->addError(
                        new FormError(
                            "Valoarea maxima transferabila din gestiunea '{$sale->getFromStorage()->getName()}' este {$maxQty}"
                        )
                    );
            }
        }


        if ($form->isValid()) {

            $em->persist($entity);
            $em->flush();

            if ($form->get('submitReturn')->isClicked()) {
                // probably redirect to the add page again

                $entity = new Sales();
                $newForm = $this->createCreateForm($entity);
                $newForm->get('date')->setData($sale->getDate());
                $newForm->get('fromStorage')->setData($sale->getFromStorage());

                //todo: add method in service

                /** @var StorageService $storageService */
                $storageService = $this->get('minico.silver.storage_service');
                $products = $storageService->getProductsForForm($sale->getFromStorage()->getId());

                $newForm
                    ->add('quantity')
                    ->add(
                        'productId',
                        'entity',
                        array(
                            'class' => 'MinicoSilverBundle:Products',
                            'choices'     => $products,
                            'empty_value' => 'Choose an option',
                        )
                    );

                /** @var Sales[] $oldSales */
                $oldSales = $saleRepo->findBy(
                    array(), array('id'=>'desc'), $limit
                );

                return array(
                    'entity' => $entity,
                    'form'   => $newForm->createView(),
                    'oldSales' => $oldSales
                );
            }

            return $this->redirect($this->generateUrl('sales_show', array('id' => $entity->getId())));
        }


        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'oldSales' => $oldSales
        );
    }

    /**
    * Creates a form to create a Sales entity.
    *
    * @param Sales $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(Sales $entity)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var StorageService $storageService */
        $storageService = $this->get('minico.silver.storage_service');
        $form = $this->createForm(new SalesType($em, $storageService), $entity, array(
            'action' => $this->generateUrl('sales_create'),
            'method' => 'POST',
        ));

        $form->add('submit', SubmitType::class, array('label' => 'Create'));
        $form->add('submitReturn', SubmitType::class, array('label' => 'Create & return'));

        return $form;
    }

    /**
     * Displays a form to create a new Sales entity.
     *
     * @Route("/new", name="sales_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Sales();
        $form   = $this->createCreateForm($entity);
        $em = $this->getDoctrine()->getManager();
        /** @var SalesRepository $saleRepo */
        $saleRepo = $em->getRepository('MinicoSilverBundle:Sales');
        $limit = 10;
        /** @var Sales[] $oldSales */
        $oldSales = $saleRepo->findBy(
            array(), array('date'=>'desc'), $limit
        );

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'oldSales' => $oldSales
        );
    }

    /**
     * Finds and displays a Sales entity.
     *
     * @Route("/{id}", name="sales_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MinicoSilverBundle:Sales')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Sales entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Sales entity.
     *
     * @Route("/{id}/edit", name="sales_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MinicoSilverBundle:Sales')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Sales entity.');
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
    * Creates a form to edit a Sales entity.
    *
    * @param Sales $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Sales $entity)
    {
        $em = $this->getDoctrine()->getManager();
        $storageId  = $entity->getFromStorage()->getId();

        /** @var ProductsRepository $productRepo */
        $productRepo = $em->getRepository('MinicoSilverBundle:Products');

        /** @var StorageService $storageService */
        $storageService = $this->get('minico.silver.storage_service');

        $productsArray = $storageService->getStorageQty($storageId);
        $products = array();
        foreach ($productsArray as $key => $productElem) {
            if ($productElem['qty'] > 0) {
                /** @var Products $prod */
                $prod = $productRepo->find($key);
                $products[$key] = $prod;
            }
        }

        $products[$entity->getProductId()->getId()] = $entity->getProductId();

        $form = $this->createForm(new SalesEditType($em, $products, $entity), $entity, array(
            'action' => $this->generateUrl('sales_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', SubmitType::class, array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Sales entity.
     *
     * @Route("/{id}", name="sales_update")
     * @Method("PUT")
     * @Template("MinicoSilverBundle:Sales:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MinicoSilverBundle:Sales')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Sales entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        /** @var Sales $sale */
        $sale = $editForm->getData();

        /** @var TransferService $transferService */
        $transferService = $this->get('minico.silver.transfer_service');
        $maxQty = $transferService
            ->getTransferMaxQty(
                $sale->getFromStorage(),
                $sale->getProductId()
            );

        $remaining = $maxQty; //- $sale->getQuantity();

        if ($remaining < 0) {
            $editForm
                ->get('quantity')
                ->addError(
                    new FormError(
                        "Valoarea maxima transferabila din gestiunea '{$sale->getFromStorage()->getName()}' este {$maxQty}"
                    )
                );
        }

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('sales_show', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Sales entity.
     *
     * @Route("/{id}", name="sales_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('MinicoSilverBundle:Sales')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Sales entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('sales'));
    }

    /**
     * Creates a form to delete a Sales entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('sales_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', SubmitType::class, array('label' => 'Delete'))
            ->getForm()
        ;
    }
    
    public function searchByPnAction()
    {
        return $this->render('MinicoSilverBundle:Sales:searchpn.html.twig', array());
    }
    
    
    public function filterSearchByPnAction(Request $request)
    {
        //@todo: add photo / noimgage if not found in db
        //resultSearchPn.html
        $message = array();
        if (!$request->isMethod('POST')) {
            $message['error'] = 'Metoda nu este POST!!!';
        }
        
        $productId = $request->get('productId');

        if (empty($productId)) {
            $message['error'] = 'Valoarea trimisa este nula!!!';
        }

        /** @var ProductsService $productService */
        $productService = $this->container->get('minico.silver.products_service');

        $productsAndQuantity = $productService->getProductsAndQuantity($productId);

        if (count($productsAndQuantity) == 0) {
            $message['error'] = 'Produsul cautat nu a fost gasit!!!';
        }

        if (array_key_exists('error', $message)) {
            return new Response(json_encode($message));
        } else {
            $image_url_path = $this->container->getParameter('image_url_path');
            return $this->render(
                'MinicoSilverBundle:Sales:resultSearchPn.html.twig', 
                array(
                    'products'          =>$productsAndQuantity,
                    'image_url_path'   => $image_url_path
                    )
                );
        }
    }

    public function saveSalesWithdrawsEntriesAction(Request $request)
    {
        $em = $this->getDoctrine()->getEntityManager();

        try {
            $em->getConnection()->beginTransaction();
            $message = array();
            if (!$request->isMethod('POST')) {
                $message['error'] = 'Metoda nu este POST!!!';
            }

            $productId      = $request->get('productId');
            $saleValue      = $request->get('saleValue');
            $withdrawValue  = $request->get('withdrawValue');
            $entriesValue   = $request->get('entriesValue');

            /**@var \MinicoSilverBundle\Entity\Products $product */
            $product = $em
                ->getRepository('MinicoSilverBundle:Products')
                ->findOneById($productId);

            try {
                $date = new \DateTime($request->get('date'));//'2000-01-01'
            } catch (Exception $e) {
                $message['error'] =  $e->getMessage();
                exit(1);
            }

            if (empty($productId)) {
                $message['error'] = 'Nu a fost trimisa nici o data!!!';
            }

            if (!empty($saleValue)) {

                /** @var \MinicoSilverBundle\Entity\Sales $sales */
                $sales = new Sales();

                $sales->setProductId($product)
                    ->setQuantity($saleValue)
                    ->setDate($date);

                $em->persist($sales);
            }

            if (!empty($withdrawValue)) {

                /** @var \MinicoSilverBundle\Entity\Withdrawls $withdraw */
                $withdraw = new Withdrawls();

                $withdraw->setProductId($product)
                    ->setQuantity($withdrawValue)
                    ->setDate($date);

                $em->persist($withdraw);
            }

            if (!empty($entriesValue)) {

                /** @var \MinicoSilverBundle\Entity\Entries $entries */
                $entries = new Entries();

                $entries->setProductId($product)
                    ->setQuantity($entriesValue)
                    ->setDate($date);

                $em->persist($entries);
            }

            $em->flush();
            $em->getConnection()->commit();
            if (array_key_exists('error', $message)) {
                return new Response(json_encode($message));
            } else {
                $message['succes'] = 'Success';
                return new Response(json_encode($message));
            }
        } catch (\Exception $e) {
            $em->getConnection()->rollback();
            throw $e;
        }
    }

    public function sellingItemsAction(Request $request)
    {
        if ($request->getMethod() == "POST") {
            $postData = current($request->request->all());

            /** @var StorageService $storageService */
            $storageService = $this->get('minico.silver.storage_service');
            /** @var Storage $storage */
            $storage = $this
                ->getDoctrine()
                ->getManager()
                ->getRepository('MinicoSilverBundle:Storage')
                ->findOneBy(
                    array(
                        'name' => $postData
                    )
                );
            $products = $storageService->getStorageQty($storage);
        }

        $productsRepo = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('MinicoSilverBundle:Products');

        $productsArray = array();
        foreach ($products as $currentProdKey => $prodQty) {
            /** @var Products $product */
            $product = $productsRepo->find($currentProdKey);
            $productsArray[$currentProdKey] = $product->__toString();
        }

        return new Response(json_encode($productsArray));
    }
}
