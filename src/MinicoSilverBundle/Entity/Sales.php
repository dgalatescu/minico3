<?php

namespace MinicoSilverBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Sales
 *
 * @ORM\Table(name="sales")
 * @ORM\Table(indexes=
 *      {
 *          @ORM\Index(name="i_date", columns={"date"})
 *      }
 * )
 * @ORM\Entity(repositoryClass="MinicoSilverBundle\Entity\SalesRepository")
 */
class Sales
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
     * @ORM\ManyToOne(targetEntity="Products", inversedBy="sales", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="productId", referencedColumnName="id")
     * @Assert\NotNull(message="")
     */
    private $productId;

    /**
     * @var integer
     * @Assert\NotNull(message="")
     * @Assert\NotBlank(message="")
     * @ORM\Column(name="quantity", type="smallint")
     */
    private $quantity;

    /**
     * @var \DateTime
     * @Assert\NotNull(message="NU poate fi nul")
     * @Assert\NotBlank(message="NU poate fi nul")
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity="Storage", inversedBy="salesFrom", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="storageFrom", referencedColumnName="id")
     * @Assert\NotNull(message="NU poate fi nul")
     * @Assert\NotBlank(message="NU poate fi nul")
     */
    private $fromStorage;

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
     * @param $productId
     * @return $this
     */
    public function setProductId($productId)
    {
        $this->productId = $productId;
    
        return $this;
    }

    /**
     * @return Products
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * Set quantity
     *
     * @param integer $quantity
     * @return Sales
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    
        return $this;
    }

    /**
     * Get quantity
     *
     * @return integer 
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return Sales
     */
    public function setDate($date)
    {
        $this->date = $date;
    
        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime 
     */
    public function getDate()
    {
        return $this->date;
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
     * @return Sales
     */
    public function setFromStorage($fromStorage)
    {
        $this->fromStorage = $fromStorage;
        return $this;
    }
}