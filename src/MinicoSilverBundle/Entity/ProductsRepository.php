<?php

namespace MinicoSilverBundle\Entity;

use Doctrine\ORM\EntityRepository;
use MinicoSilverBundle\Entity\Products;

/**
 * ProductsRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ProductsRepository extends EntityRepository
{
    public function getProductsAndQuantityByProductId(Products $product)
    {

        $query = $this->createQueryBuilder('prod')
            ->select('prod.id, prod.productCode, prod.productDescription, prod.entryPrice, prod.salePrice, prod.photo')
            ->addSelect('category.name')
            ->join('MinicoSilverBundle:Category', 'category', 'WITH', 'category.id = prod.category')
            ->where('prod.id = :productId')
            ->setParameter('productId', $product);
        return $query;
    }

    public function getSumEntries(Products $product)
    {
        $query = $this->createQueryBuilder('prod')
            ->select('prod.id, prod.productCode, prod.productDescription, prod.entryPrice, prod.salePrice')
            ->addSelect('sum(entries.quantity) as maxEntity')
            ->join('MinicoSilverBundle:Entries', 'entries', 'WITH', 'entries.productId = prod.id')
            ->where('prod.id = :productId')
            ->setParameter('productId', $product)
            ->groupBy('prod.id');
        return $query;
    }

    public function getSumSales(Products $product)
    {
        $query = $this->createQueryBuilder('prod')
            ->select('prod.id')
            ->addSelect('sum(sales.quantity) as maxSales')
            ->leftJoin('MinicoSilverBundle:Sales', 'sales', 'WITH', 'sales.productId = prod.id')
            ->where('prod.id = :productId')
            ->setParameter('productId', $product)
            ->groupBy('prod.id');
        return $query;
    }

    public function getSumWithdrawls(Products $product)
    {
        $query = $this->createQueryBuilder('prod')
            ->select('prod.id')
            ->addSelect('sum(withdrawls.quantity) as maxWithdrawls')
            ->leftJoin('MinicoSilverBundle:Withdrawls', 'withdrawls', 'WITH', 'withdrawls.productId = prod.id')
            ->where('prod.id = :productId')
            ->setParameter('productId', $product)
            ->groupBy('prod.id');
        return $query;
    }

    public function findByLikeProductCode($code)
    {
        $query = $this->createQueryBuilder('prod')
            ->where('prod.productCode LIKE :productCode')
            ->setParameter('productCode', "$code%")
            ->orderBy('prod.productCode');
        return $query
            ->getQuery()
            ->getResult();
    }
}