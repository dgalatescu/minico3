<?php

namespace MinicoSilverBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Products
 *
 * @ORM\Table(name="products")
 * @ORM\Table(indexes={@ORM\Index(name="i_id_productCode_supplier", columns={"id", "productCode", "supplierId"})})
 * @ORM\Entity(repositoryClass="MinicoSilverBundle\Entity\ProductsRepository")
 */
class Products
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
     * @ORM\Column(name="productCode", type="string", length=20)
     */
    private $productCode;

    /**
     * @var string
     *
     * @ORM\Column(name="productDescription", type="string", length=100, nullable=true)
     */
    private $productDescription;

    /**
     * @var float
     *
     * @ORM\Column(name="entryPrice", type="float")
     */
    private $entryPrice;
    
    /**
     * @var float
     *
     * @ORM\Column(name="salePrice", type="float")
     */
    private $salePrice;

    /**
     * @ORM\ManyToOne(targetEntity="Suppliers", inversedBy="products", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="supplierId", referencedColumnName="id")
     */
    private $supplier;
    
    /**
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="products", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="categoryId", referencedColumnName="id")
     */
    private $category;

    /**
     * @var string
     *
     * @ORM\Column(name="photo", type="string", nullable=true)
     */
    private $photo;

    /**
     * @var Entries[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="Entries", mappedBy="productId", fetch="EXTRA_LAZY")
     */
    private $entries;

    /**
     * @var Sales[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="Sales", mappedBy="productId", fetch="EXTRA_LAZY")
     */
    private $sales;

    /**
     * @var Withdrawls[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="Withdrawls", mappedBy="productId", fetch="EXTRA_LAZY")
     */
    private $withdraws;

    /**
     * @var Transfer[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="Transfer", mappedBy="product", fetch="EXTRA_LAZY")
     */
    private $transfers;

    public function __construct()
    {
        $this->entries = new ArrayCollection();
        $this->sales = new ArrayCollection();
        $this->withdraws = new ArrayCollection();
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
     * Set productCode
     *
     * @param string $productCode
     * @return Products
     */
    public function setProductCode($productCode)
    {
        $this->productCode = $productCode;
    
        return $this;
    }

    /**
     * Get productCode
     *
     * @return string 
     */
    public function getProductCode()
    {
        return $this->productCode;
    }

    /**
     * Set productDescription
     *
     * @param string $productDescription
     * @return Products
     */
    public function setProductDescription($productDescription)
    {
        $this->productDescription = $productDescription;
    
        return $this;
    }

    /**
     * Get productDescription
     *
     * @return string 
     */
    public function getProductDescription()
    {
        return $this->productDescription;
    }

    /**
     * Set entryPrice
     *
     * @param float $entryPrice
     * @return Products
     */
    public function setEntryPrice($entryPrice)
    {
        $this->entryPrice = $entryPrice;
    
        return $this;
    }

    /**
     * Get entryPrice
     *
     * @return float 
     */
    public function getEntryPrice()
    {
        return $this->entryPrice;
    }

    /**
     * Set salePrice
     *
     * @param float $salePrice
     * @return Products
     */
    public function setSalePrice($salePrice)
    {
        $this->salePrice = $salePrice;
    
        return $this;
    }

    /**
     * Get salePrice
     *
     * @return float 
     */
    public function getSalePrice()
    {
        return $this->salePrice;
    }

    /**
     * Set supplier
     *
     * @param \MinicoSilverBundle\Entity\Suppliers $supplier
     * @return Products
     */
    public function setSupplier(\MinicoSilverBundle\Entity\Suppliers $supplier = null)
    {
        $this->supplier = $supplier;
    
        return $this;
    }

    /**
     * Get supplier
     *
     * @return \MinicoSilverBundle\Entity\Supplier
     */
    public function getSupplier()
    {
        return $this->supplier;
    }

    /**
     * Set category
     *
     * @param \MinicoSilverBundle\Entity\Category $category
     * @return Products
     */
    public function setCategory(\MinicoSilverBundle\Entity\Category $category = null)
    {
        $this->category = $category;
    
        return $this;
    }

    /**
     * Get category
     *
     * @return \MinicoSilverBundle\Entity\Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param string $photo
     * @return $this
     */
    public function setPhoto($photo)
    {
        $this->photo = $photo;

        return $this;
    }

    /**
     * @return string
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    public function __toString()
    {
        return $this->getProductCode().' - '.$this->getCategory(). ' - '.$this->getSalePrice();
    }

    /**
     * @return mixed
     */
    public function getEntries()
    {
        return $this->entries;
    }

    /**
     * @param mixed $entries
     * @return Products
     */
    public function setEntries($entries)
    {
        $this->entries = $entries;
        return $this;
    }

    /**
     * @param Entries $entry
     * @return $this
     */
    public function addEntry(Entries $entry)
    {
        $this->entries[] = $entry;

        return $this;
    }

    /**
     * @param Entries $entry
     * @return $this
     */
    public function removeEntry(Entries $entry)
    {
        $this->entries->removeElement($entry);

        return $this;
    }

    /**
     * @return ArrayCollection|Sales[]
     */
    public function getSales()
    {
        return $this->sales;
    }

    /**
     * @param ArrayCollection|Sales[] $sales
     * @return Products
     */
    public function setSales($sales)
    {
        $this->sales = $sales;
        return $this;
    }

    /**
     * @param Sales $sale
     * @return $this
     */
    public function addSale(Sales $sale)
    {
        $this->sales[] = $sale;

        return $this;
    }

    /**
     * @param Sales $sale
     * @return $this
     */
    public function removeSale(Sales $sale)
    {
        $this->sales->removeElement($sale);

        return $this;
    }

    /**
     * @return ArrayCollection|Withdrawls[]
     */
    public function getWithdraws()
    {
        return $this->withdraws;
    }

    /**
     * @param ArrayCollection|Withdrawls[] $withdraws
     * @return Products
     */
    public function setWithdraws($withdraws)
    {
        $this->withdraws = $withdraws;
        return $this;
    }

    /**
     * @param Withdrawls $withdraws
     * @return $this
     */
    public function addWithdraws(Withdrawls $withdraws)
    {
        $this->withdraws[] = $withdraws;

        return $this;
    }

    /**
     * @param Withdrawls $withdraws
     * @return $this
     */
    public function removeWithdraws(Withdrawls $withdraws)
    {
        $this->withdraws->removeWithdraws($withdraws);

        return $this;
    }

    /**
     * @return ArrayCollection|Transfer[]
     */
    public function getTransfers()
    {
        return $this->transfers;
    }

    /**
     * @param ArrayCollection|Transfer[] $transfers
     * @return Products
     */
    public function setTransfers($transfers)
    {
        $this->transfers = $transfers;
        return $this;
    }

    /**
     * @param Transfer $transfer
     * @return $this
     */
    public function addTransfers(Transfer $transfer)
    {
        $this->transfers[] = $transfer;

        return $this;
    }

    /**
     * @param Transfer $transfer
     * @return $this
     */
    public function removeTransfer(Transfer $transfer)
    {
        $this->withdraws->removeWithdraws($transfer);

        return $this;
    }
}