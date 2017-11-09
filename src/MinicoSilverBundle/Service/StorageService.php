<?php
namespace MinicoSilverBundle\Service;

use Doctrine\Bundle\DoctrineBundle\Registry;
use MinicoSilverBundle\Entity\ProductsRepository;
use MinicoSilverBundle\Entity\Storage;
use MinicoSilverBundle\Entity\StorageRepository;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;

class StorageService
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @var \Doctrine\Bundle\DoctrineBundle\Registry
     */
    protected $doctrine;

    /**
     * @var Translator
     */
    protected $translator;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->doctrine = $this->container->get('doctrine');
    }

    /**
     * @return Container
     */
    protected function getContainer()
    {
        return $this->container;
    }

    /**
     * returns array('qty')
     * @param $storageId
     * @return mixed
     */
    public function getStorageQty($storageId)
    {
        $em = $this->doctrine->getManager();

        /** @var StorageRepository $storageRepo */
        $storageRepo = $em->getRepository('MinicoSilverBundle:Storage');
        /** @var Storage $storage */
        $storage = $storageRepo->find($storageId);

        if ($storage->getMainStorage()) {

            /*
             * -- sum(e.quantity) - sum(w.quantity) - sum(f.qty)
select *, (e1.s - IF(w1.s is null,0,w1.s) - IF(t1.s is null,0,t1.s)) as suma from
(select e.productId as pid, sum(e.quantity) as s from entries e where e.storageId = 1 group by e.productId) as e1
left join (select w.productId as pid, sum(w.quantity) as s from withdrawls w where w.storageId = 1 group by w.productId) as w1
ON e1.pid = w1.pid
left join (select t.productId as pid, sum(t.quantity) as s from transfer t where t.storageFrom = 1 group by t.productId) as t1
ON t1.pid = e1.pid
             */
            //sum(e.quantity) - sum(w.quantity) - sum(f.qty)
//            $productsEntries = $storageRepo
//                ->getSumEntriesForMainStorage($storage);
//            $productsWithdraw = $storageRepo
//                ->getSumWithdrawsForMainStorage($storage);
//            $productsTransfers = $storageRepo
//                ->getSumTransferFromForMainStorage($storage);
//
//            foreach ($productsEntries as $currentEntry) {
//                $products[$currentEntry['productId']]['qty'] = $currentEntry['result'];
//            }
//
//            foreach ($productsTransfers as $currentTransfer) {
//                if (array_key_exists($currentTransfer['productId'], $products)) {
//                    $products[$currentTransfer['productId']]['qty'] -= $currentTransfer['result'];
//                }
//            }
//
//            foreach ($productsWithdraw as $currentWithdraw) {
//                if (array_key_exists($currentWithdraw['productId'], $products)) {
//                    $products[$currentWithdraw['productId']]['qty'] -= $currentWithdraw['result'];
//                }
//            }
            $productsEntries = $storageRepo
                ->getProductsOfMainStorage($storage);

            foreach ($productsEntries as $currentEntry) {
                $products[$currentEntry['productId']]['qty'] = $currentEntry['result'];
            }
        } elseif ($storage->getSellingStorage()) {
/*
 * //storageTo - storageFrom - sale
select *, (st1.s - IF(sf1.s is null,0,sf1.s) - IF(sa1.s is null,0,sa1.s)) as suma from
	(select st.productId as pid, sum(st.quantity) as s from transfer st where st.storageTo = 2 group by st.productId) as st1
	left join (
		select t.productId as pid, sum(t.quantity) as s from transfer t where t.storageFrom = 2 group by t.productId) as sf1
			ON st1.pid = sf1.pid
	left join (
		select sa.productId as pid, sum(sa.quantity) as s from sales sa where sa.storageFrom = 2 group by sa.productId) as sa1
			ON st1.pid = sa1.pid;

 */
            $productsTransferTo = $storageRepo
                ->getProductsOfMainStorage($storage);
            //storageTo - storageFrom - sale
//            $productsTransferTo = $storageRepo
//                ->getSumTransferToForMainStorage($storage);
//            $productsTransferTo = $storageRepo
//                ->getSumTransferToForMainStorage($storage);
//
//            $productsTransfersFrom = $storageRepo
//                ->getSumTransferFromForMainStorage($storage);
//            $productsSales = $storageRepo
//                ->getProductsForSellingStorage($storage);
//
            foreach ($productsTransferTo as $currentTransferTo) {
                $products[$currentTransferTo['productId']]['qty'] = $currentTransferTo['result'];
            }
//
//            foreach ($productsTransfersFrom as $currentTransferFrom) {
//                if (array_key_exists($currentTransferFrom['productId'], $products)) {
//                    $products[$currentTransferFrom['productId']]['qty'] -= $currentTransferFrom['result'];
//                }
//            }
//
//            foreach ($productsSales as $currentProductsSale) {
//                if (array_key_exists($currentProductsSale['productId'], $products)) {
//                    $products[$currentProductsSale['productId']]['qty'] -= $currentProductsSale['result'];
//                }
//            }
        }

        return $products;
    }

    /**
     * @param $storage
     * @return array
     */
    public function getProductsForForm($storage)
    {
        $em = $this->doctrine->getManager();
        /** @var ProductsRepository $productRepo */
        $productRepo = $em->getRepository('MinicoSilverBundle:Products');

        $productsArray = $this->getStorageQty($storage);
        $products = array();
        foreach ($productsArray as $key => $productElem) {
            if ($productElem['qty'] > 0) {
                /** @var Products $prod */
                $prod = $productRepo->find($key);
                $products[$key] = $prod;
            }
        }
        return $products;
    }
} 