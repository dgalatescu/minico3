<?php

namespace MinicoSilverBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Suppliers
 *
 * @ORM\Table(name="suppliers")
 * @ORM\Entity(repositoryClass="MinicoSilverBundle\Entity\SuppliersRepository")
 */
class Suppliers
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=10)
     */
    private $code;

    /**
     * @var Category[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="Products", mappedBy="supplier", fetch="EXTRA_LAZY")
     */
    private $products;

    public function __construct()
    {
        $this->products = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Suppliers
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Set code
     *
     * @param string $code
     * @return Suppliers
     */
    public function setCode($code)
    {
        $this->code = $code;
    
        return $this;
    }

    /**
     * Get code
     *
     * @return string 
     */
    public function getCode()
    {
        return $this->code;
    }
    
    public function __toString()
    {
        return $this->name;
    }

    /**
     * @return ArrayCollection|Category[]
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * @param ArrayCollection|Category[] $products
     * @return Suppliers
     */
    public function setProducts($products)
    {
        $this->products = $products;
        return $this;
    }

    /**
     * @param Products $product
     * @return $this
     */
    public function addProducts(Products $product)
    {
        $this->products[] = $product;
        return $this;
    }

    /**
     * @param Products $product
     * @return $this
     */
    public function removeProducts(Products $product)
    {
        $this->products->removeElement($product);
        return $this;
    }
}