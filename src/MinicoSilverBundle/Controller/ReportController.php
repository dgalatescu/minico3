<?php

namespace MinicoSilverBundle\Controller;

use Doctrine\ORM\EntityRepository;
use MinicoSilverBundle\Entity\Products;
use MinicoSilverBundle\Entity\ProductsRepository;
use MinicoSilverBundle\Entity\SalesRepository;
use MinicoSilverBundle\Entity\Storage;
use MinicoSilverBundle\Entity\StorageRepository;
use MinicoSilverBundle\Entity\Suppliers;
use MinicoSilverBundle\Service\StorageService;
use Proxies\__CG__\MinicoSilverBundle\Entity\Sales;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ReportController extends Controller {

    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $products = array();
        $defaultData = array('message' => 'Type here');
        $form = $this
            ->createFormBuilder($defaultData)
            ->add(
                'Storage',
                'entity',
                array(
                    'empty_data' => 'Choose an option',
                    'empty_value' => null,
                    'class' => 'MinicoSilverBundle:Storage',
                    'query_builder' => function(EntityRepository $er) {
                        return $er
                            ->createQueryBuilder('s')
                            ->orderBy('s.name', 'ASC');
                    },
                    'data' => null
                )
            )
            ->add(
                'Suppliers',
                'entity',
                array(
                    'class' => 'MinicoSilverBundle:Suppliers',
                    'query_builder' => function(EntityRepository $er) {
                        return $er
                            ->createQueryBuilder('s')
                            ->orderBy('s.name', 'ASC');
                    },
                    'data' => null,
                    'multiple' => true,
                    'required' => false
                )
            )
            ->add('submit','submit')
            ->getForm();
        $suppliers = array();
        if ($request->getMethod() == "POST") {
            $form->submit($request);
            if ($form->isValid()) {
                $postData = current($request->request->all());

                /** @var StorageService $storageService */
                $storageService = $this->get('minico.silver.storage_service');
                if (!empty($postData['Suppliers'])) {
                    $suppliers = $postData['Suppliers'];
                }
                $products = $storageService->getStorageQty($postData['Storage']);
            }
        }

        /** @var ProductsRepository $productRepo */
        $productRepo = $em->getRepository('MinicoSilverBundle:Products');
        foreach ($products as $currentProdKey => $prodQty) {
            $products[$currentProdKey]['product'] = $productRepo->find($currentProdKey);
        }

        return $this->render(
            'MinicoSilverBundle:Report:index.html.twig',
            array(
                'form' => $form->createView(),
                'products' => $products,
                'suppliers' => $suppliers
            )
        );
    }

    public function supplierReportAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var StorageRepository $storageRepo */
        $storageRepo = $em->getRepository('MinicoSilverBundle:Storage');

        /** @var Storage $storage */
        $storage = $storageRepo->find(1);
        //entries - withdraw - sold
        $productsEntries = $storageRepo
            ->getSumEntriesForMainStorage($storage);
        $productsWithdraw = $storageRepo
            ->getSumWithdrawsForMainStorage($storage);

        /** @var Storage[] $sellingStorages */
        $sellingStorages = $storageRepo->findBy(
            array(
                'sellingStorage' => 1
            )
        );

        foreach ($productsEntries as $currentEntry) {
            $products[$currentEntry['productId']]['qty'] = $currentEntry['result'];
        }

        foreach ($productsWithdraw as $currentWithdraw) {
            if (array_key_exists($currentWithdraw['productId'], $products)) {
                $products[$currentWithdraw['productId']]['qty'] -= $currentWithdraw['result'];
            }
        }

        /** @var Storage $currentSellingStorage */
        foreach ($sellingStorages as $currentSellingStorage) {
            $productsSales = $storageRepo
                ->getProductsForSellingStorage(
                    $currentSellingStorage
                );

            foreach ($productsSales as $currentProductsSale) {
                if (array_key_exists($currentProductsSale['productId'], $products)) {
                    $products[$currentProductsSale['productId']]['qty'] -= $currentProductsSale['result'];
                }
            }
        }

        /** @var ProductsRepository $productRepo */
        $productRepo = $em->getRepository('MinicoSilverBundle:Products');

        foreach ($products as $currentProdKey => $prodQty) {
            $products[$currentProdKey]['product'] = $productRepo->find($currentProdKey);
        }

        $sumPerSupplier = array();

        foreach ($products as $currentProdKey => $product) {
            /** @var Products $currentProduct */
            $currentProduct = $products[$currentProdKey]['product'];
            /** @var Suppliers $supplier */
            $supplier = $currentProduct->getSupplier();
            if (!array_key_exists($supplier->getId(), $sumPerSupplier)) {
                $sumPerSupplier[$supplier->getId()]['name'] = $supplier->getName();
                $sumPerSupplier[$supplier->getId()]['value'] = 0;
            }

            $sumPerSupplier[$supplier->getId()]['value'] +=
                $currentProduct->getEntryPrice() * $products[$currentProdKey]['qty'];
        }

        return $this->render(
            'MinicoSilverBundle:Report:supplier.html.twig',
            array(
                'sumPerSupplier' => $sumPerSupplier
            )
        );
    }

    public function productCodeSalesReportAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $message = null;
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
            ->add('submit','submit')
            ->getForm();

        $startDate = null;
        $endDate = null;

        if ($request->getMethod() == "POST") {
            $form->submit($request);
            if ($form->isValid()) {
                $postData = current($request->request->all());

                /** @var SalesRepository $sales */
                $salesRepo = $em->getRepository('MinicoSilverBundle:Sales');

                $startDate = $postData['dateStart'];
                $endDate = $postData['dateEnd'];
                $sales = $salesRepo
                    ->getSalesByDates($startDate, $endDate)
                    ->getQuery()
                    ->getResult();
                if (count($sales) == 0) {
                    $message = 'Nu sunt vanzari in perioada selectata.';
                } else {
                    $message = "Raport vanzari pentru perioada $startDate - $endDate";
                }
            }
        } else {
            /** @var Sales[] $sales */
            $sales = array();
            $message = "Alegeti perioada pentru generarea raportului";
        }

        $resultArray = array();
        $productsArray = array();
        $totalSalesShop = array();
        /** @var Sales $currentSale */
        foreach ($sales as $currentSale) {

            $code = preg_replace("/[^a-zA-Z]+/", "", $currentSale->getProductId()->getProductCode());
            $shop = $currentSale->getFromStorage()->getName();
            $supplier = $currentSale->getProductId()->getSupplier()->getName();
            if (!array_key_exists($shop, $totalSalesShop)) {
                $totalSalesShop[$shop] = array();
            }

            if (!array_key_exists($supplier, $totalSalesShop[$shop])) {
                $totalSalesShop[$shop][$supplier] = 0;
            }
            $totalSalesShop[$shop][$supplier] += $currentSale->getProductId()->getEntryPrice();

            if (!array_key_exists($code, $resultArray)) {
                $resultArray[$code] = array();
            }

            if (!array_key_exists($shop, $resultArray[$code])) {
                $resultArray[$code][$shop] = 0;
            }
            $resultArray[$code][$shop] += $currentSale->getProductId()->getEntryPrice();
            $productsArray[$code][$shop][] = $currentSale->getProductId();
        }

        return $this->render(
            'MinicoSilverBundle:Report:salesByProductCode.html.twig',
            array(
                'sumPerCodeAndShop' => $resultArray,
                'productsArray' => $productsArray,
                'form' => $form->createView(),
                'startDate' => $startDate,
                'endDate' => $endDate,
                'message' => $message,
                'totalSalesShop' => $totalSalesShop
            )
        );

    }

    public function productCodeReportAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var StorageRepository $storageRepo */
        $storageRepo = $em->getRepository('MinicoSilverBundle:Storage');

        /** @var ProductsRepository $productRepo */
        $productRepo = $em->getRepository('MinicoSilverBundle:Products');

        $message = null;
        $defaultData = array('message' => 'Type here');

        /** @var Products[] $products */
        $products = $productRepo->findAll();
        $productCodeChoices = array();
        /** @var Products $product */
        foreach ($products as $product) {
            $code = preg_replace("/[^a-zA-Z]+/", "", $product->getProductCode());

            if (!in_array($code, $productCodeChoices)) {
                $productCodeChoices[$code] = $code;
            }
        }

        $form = $this
            ->createFormBuilder($defaultData)
            ->add(
                'code',
                'choice',
                array(
                    'choices'   => $productCodeChoices,
                    'required'  => true,
                )
            )
            ->add('submit','submit')
            ->getForm();

        $code = null;
        if ($request->getMethod() == "POST") {
            $form->submit($request);
            if ($form->isValid()) {
                $postData = current($request->request->all());

                $code = $postData['code'];
                /** @var Products[] $products */
                $products = $productRepo->findByLikeProductCode($code);
                if (count($products) == 0) {
                    $message = 'Nu s-au gasit produse dupa codul specificat.';
                } else {
                    $message = "Raport produse cod '{$code}'";
                }
            }
        } else {
            $message = "Alegeti codul pentru generarea raportului";
        }

        $productsArray = array();
        if (!empty($code) && count($products) > 0) {
            $results = array();
            /** @var Storage $mainStorage */
            $mainStorage = $storageRepo->find(Storage::ID_MAIN_STORAGE);
            $drumulTabereiStorage = $storageRepo->find(Storage::ID_DRUMUL_TABEREI);
            $drumetuStorage = $storageRepo->find(Storage::ID_DRUMETU);
            foreach ($products as $product) {
                $results['productId'] = $product->getId();
                $results['productCode'] = $product->__toString();
                //todo: + transfer to main
                $entries = $storageRepo->getSumEntriesForMainStorage($mainStorage, $product);
                $results['entriesMainStorage'] = !empty($entries['result']) ? $entries['result'] : 0;
                $withdraws = $storageRepo->getSumWithdrawsForMainStorage($mainStorage, $product);
                $results['withdrawMainStorage'] = !empty($withdraws['result']) ? $withdraws['result'] : 0;

                $drumulTabereiTo = $storageRepo->getSumTransferToForMainStorage($drumulTabereiStorage, $product);
                $results['entriesDrumulTaberei'] = !empty($drumulTabereiTo['result']) ? $drumulTabereiTo['result'] : 0;
                $drumulTabereiSale = $storageRepo->getProductsForSellingStorage($drumulTabereiStorage, $product);
                $results['salesDrumulTaberei'] = !empty($drumulTabereiSale['result']) ? $drumulTabereiSale['result'] : 0;

                $drumetuTo = $storageRepo->getSumTransferToForMainStorage($drumetuStorage, $product);
                $results['entriesDrumetu'] = !empty($drumetuTo['result']) ? $drumetuTo['result'] : 0;
                $drumetuSale = $storageRepo->getProductsForSellingStorage($drumetuStorage, $product);
                $results['salesDrumetu'] = !empty($drumetuSale['result']) ? $drumetuSale['result'] : 0;

                $productsArray[] = $results;
                if (count($productsArray) > 100) {
                    break;
                }
            }
        }

        return $this->render(
            'MinicoSilverBundle:Report:productCode.html.twig',
            array(
                'form' => $form->createView(),
                'message' => $message,
                'productsArray' => $productsArray
            )
        );

    }
}
