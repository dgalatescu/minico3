<?php

namespace MinicoSilverBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Withdrawls
 *
 * @ORM\Table(name="withdrawls")
 * @ORM\Entity(repositoryClass="MinicoSilverBundle\Entity\WithdrawlsRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Withdrawls
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
     * @ORM\ManyToOne(targetEntity="Products", inversedBy="withdraws", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="productId", referencedColumnName="id")
     * @Assert\NotNull(message="NU poate fi nul")
     * @Assert\NotBlank(message="NU poate fi nul")
     */
    private $productId;

    /**
     * @var integer
     *
     * @ORM\Column(name="quantity", type="smallint")
     * @Assert\NotNull(message="NU poate fi nul")
     * @Assert\NotBlank(message="NU poate fi nul")
     */
    private $quantity;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity="Storage", inversedBy="withdraws", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="storageId", referencedColumnName="id")
     */
    private $storage;

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
     * Set productId
     *
     * @param integer $productId
     * @return Withdrawls
     */
    public function setProductId($productId)
    {
        $this->productId = $productId;
    
        return $this;
    }

    /**
     * Get productId
     *
     * @return integer 
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * Set quantity
     *
     * @param integer $quantity
     * @return Withdrawls
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
     * @return Withdrawls
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
     * @return mixed
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * @param mixed $storage
     * @return Withdrawls
     */
    public function setStorage($storage)
    {
        $this->storage = $storage;
        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedAtValue()
    {
        $this->date = new \DateTime();
    }
}
