<?php

namespace MinicoSilverBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Transfer
 *
 * @ORM\Table()
 * @ORM\Table(indexes=
 *      {
 *          @ORM\Index(name="i_product_fromStorage_toStorage", columns={"productId", "storageFrom", "storageTo" }),
 *      }
 * )
 * @ORM\Entity(repositoryClass="MinicoSilverBundle\Entity\TransferRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Transfer
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
     * @ORM\ManyToOne(targetEntity="Products", inversedBy="transfers", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="productId", referencedColumnName="id")
     */
    private $product;

    /**
     * @ORM\ManyToOne(targetEntity="Storage", inversedBy="transfersFrom", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="storageFrom", referencedColumnName="id")
     */
    private $fromStorage;

    /**
     * @ORM\ManyToOne(targetEntity="Storage", inversedBy="transfersTo", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="storageTo", referencedColumnName="id")
     */
    private $toStorage;

    /**
     * @var integer
     *
     * @ORM\Column(name="quantity", type="integer")
     */
    private $qty;

    /**
     * @var \DateTime
     * @ORM\Column(name="creationDate", type="date", nullable=false)
     */
    private $creationDate;

    /**
     * @var \DateTime
     * @ORM\Column(name="modified", type="datetime", nullable=false)
     */
    private $modified;


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
     * Set storageId
     *
     * @param integer $storageId
     * @return Transfer
     */
    public function setStorageId($storageId)
    {
        $this->storageId = $storageId;
    
        return $this;
    }

    /**
     * Get storageId
     *
     * @return integer 
     */
    public function getStorageId()
    {
        return $this->storageId;
    }

    /**
     * @return int
     */
    public function getQty()
    {
        return $this->qty;
    }

    /**
     * @param int $qty
     * @return Transfer
     */
    public function setQty($qty)
    {
        $this->qty = $qty;
        return $this;
    }

    /**
     * Set creationDate
     *
     * @param \DateTime $creationDate
     * @return Transfer
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    
        return $this;
    }

    /**
     * Get creationDate
     *
     * @return \DateTime 
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Set modified
     *
     * @param \DateTime $modified
     * @return Transfer
     */
    public function setModified($modified)
    {
        $this->modified = $modified;
    
        return $this;
    }

    /**
     * Get modified
     *
     * @return \DateTime 
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * @return Products
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param mixed $product
     * @return Transfer
     */
    public function setProduct($product)
    {
        $this->product = $product;
        return $this;
    }

    /**
     * @return Storage
     */
    public function getFromStorage()
    {
        return $this->fromStorage;
    }

    /**
     * @param mixed $fromStorage
     * @return Transfer
     */
    public function setFromStorage($fromStorage)
    {
        $this->fromStorage = $fromStorage;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getToStorage()
    {
        return $this->toStorage;
    }

    /**
     * @param mixed $toStorage
     * @return Transfer
     */
    public function setToStorage($toStorage)
    {
        $this->toStorage = $toStorage;
        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedAtValue()
    {
        $this->creationDate = new \DateTime();
        $this->modified = new \DateTime();
    }
}
