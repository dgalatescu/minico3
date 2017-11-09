<?php
namespace MinicoSilverBundle\Service;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;

class ProductsService
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
     * @param int $productId
     * @return array
     */
    public function getProductsAndQuantity($productId)
    {
        $em = $this->doctrine->getManager();

        /**@var \MinicoSilverBundle\Entity\ProductsRepository $productsRepository */
        $productsRepository = $em->getRepository('MinicoSilverBundle:Products');

        $products = $productsRepository->findBy(
            array(
                'productCode' => $productId,
            )
        );


        $productsAndQuantity = array();

        $i = 0;

        /** @var \MinicoSilverBundle\Entity\Products $product */
        foreach ($products as $product) {
            $productss = $productsRepository
                ->getProductsAndQuantityByProductId($product)
                ->getQuery()
                ->getResult();


            $productsAndQuantity[$i]['id']                  = $productss[0]['id'];
            $productsAndQuantity[$i]['productCode']         = $productss[0]['productCode'];
            $productsAndQuantity[$i]['productDescription']  = $productss[0]['productDescription'];
            $productsAndQuantity[$i]['entryPrice']          = $productss[0]['entryPrice'];
            $productsAndQuantity[$i]['salePrice']           = $productss[0]['salePrice'];
            $productsAndQuantity[$i]['categName']           = $productss[0]['name'];
            $productsAndQuantity[$i]['photo']               = $productss[0]['photo'];

            $sumEntries                                     = $productsRepository
                ->getSumEntries($product)
                ->getQuery()
                ->getResult();

            $productsAndQuantity[$i]['sumEntries']          = $sumEntries[0]['maxEntity'];

            $sumSales                                       = $productsRepository
                ->getSumSales($product)
                ->getQuery()
                ->getResult();

            $productsAndQuantity[$i]['sumSales']            = $sumSales[0]['maxSales'];

            $sumWithdrawls                                  = $productsRepository
                ->getSumWithdrawls($product)
                ->getQuery()
                ->getResult();

            $productsAndQuantity[$i]['sumWithdrawls']       = $sumWithdrawls[0]['maxWithdrawls'];


            $i++;
        }
        return $productsAndQuantity;
    }


    public function checkIfProductPresentInTable()
    {

    }
}
