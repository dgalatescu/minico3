<?php

namespace MinicoSilverBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Storage
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="MinicoSilverBundle\Entity\StorageRepository")
 */
class Storage
{
    const ID_MAIN_STORAGE = 1;
    const ID_DRUMETU = 2;
    const ID_DRUMUL_TABEREI = 3;
    const IS_MAINSTORAGE = true;
    const IS_NOT_MAINSTORAGE = false;
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=10)
     */
    private $code;

    /**
     * @var boolean
     *
     * @ORM\Column(name="sellingStorage", type="boolean", nullable=true)
     */
    private $sellingStorage;

    /**
     * @var boolean
     *
     * @ORM\Column(name="mainStorage", type="boolean", nullable=true)
     */
    private $mainStorage;

    /**
     * @var Entries[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="Entries", mappedBy="storage", fetch="EXTRA_LAZY")
     */
    private $entries;

    /**
     * @var Withdrawls[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="Withdrawls", mappedBy="storage", fetch="EXTRA_LAZY")
     */
    private $withdraws;

    /**
     * @var Transfer[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="Transfer", mappedBy="fromStorage", fetch="EXTRA_LAZY")
     */
    private $transfersFrom;

    /**
     * @var Transfer[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="Transfer", mappedBy="toStorage", fetch="EXTRA_LAZY")
     */
    private $transfersTo;

    /**
     * @var Sales[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="Sales", mappedBy="fromStorage", fetch="EXTRA_LAZY")
     */
    private $salesFrom;

    public function __construct()
    {
        $this->entries = new ArrayCollection();
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
     * @return Storage
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
     * @return Storage
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

    /**
     * Set sellingStorage
     *
     * @param boolean $sellingStorage
     * @return Storage
     */
    public function setSellingStorage($sellingStorage)
    {
        $this->sellingStorage = $sellingStorage;
    
        return $this;
    }

    /**
     * Get sellingStorage
     *
     * @return boolean 
     */
    public function getSellingStorage()
    {
        return $this->sellingStorage;
    }

    /**
     * Set mainStorage
     *
     * @param boolean $mainStorage
     * @return Storage
     */
    public function setMainStorage($mainStorage)
    {
        $this->mainStorage = $mainStorage;
    
        return $this;
    }

    /**
     * Get mainStorage
     *
     * @return boolean 
     */
    public function getMainStorage()
    {
        return $this->mainStorage;
    }

    /**
     * @return ArrayCollection|Entries[]
     */
    public function getEntries()
    {
        return $this->entries;
    }

    /**
     * @param ArrayCollection|Entries[] $entries
     * @return Storage
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
     * @return ArrayCollection|Withdrawls[]
     */
    public function getWithdraw()
    {
        return $this->withdraws;
    }

    /**
     * @param ArrayCollection|Withdrawls[] $withdraw
     * @return Storage
     */
    public function setWithdraw($withdraw)
    {
        $this->withdraws = $withdraw;
        return $this;
    }

    /**
     * @param Withdrawls $withdraw
     * @return $this
     */
    public function addWithdraw(Withdrawls $withdraw)
    {
        $this->withdraws[] = $withdraw;

        return $this;
    }

    /**
     * @param Withdrawls $withdraw
     * @return $this
     */
    public function removeWithdraw(Withdrawls $withdraw)
    {
        $this->withdraws->removeElement($withdraw);

        return $this;
    }

    /**
     * @return ArrayCollection|Transfer[]
     */
    public function getTransfersFrom()
    {
        return $this->transfersFrom;
    }

    /**
     * @param ArrayCollection|Transfer[] $transfersFrom
     * @return Storage
     */
    public function setTransfersFrom($transfersFrom)
    {
        $this->transfersFrom = $transfersFrom;
        return $this;
    }

    /**
     * @return ArrayCollection|Transfer[]
     */
    public function getTransfersTo()
    {
        return $this->transfersTo;
    }

    /**
     * @param ArrayCollection|Transfer[] $transfersTo
     * @return Storage
     */
    public function setTransfersTo($transfersTo)
    {
        $this->transfersTo = $transfersTo;
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
     * @return Storage
     */
    public function setWithdraws($withdraws)
    {
        $this->withdraws = $withdraws;
        return $this;
    }

    /**
     * @param Transfer $transferFrom
     * @return $this
     */
    public function addTransfersFrom(Transfer $transferFrom)
    {
        $this->transfersFrom[] = $transferFrom;

        return $this;
    }

    /**
     * @param Transfer $transferForm
     * @return $this
     */
    public function removeTransferFrom(Transfer $transferForm)
    {
        $this->transfersFrom->removeWithdraws($transferForm);

        return $this;
    }

    /**
     * @param Transfer $transferTo
     * @return $this
     */
    public function addTransfersTo(Transfer $transferTo)
    {
        $this->transferTo[] = $transferTo;

        return $this;
    }

    /**
     * @param Transfer $transferTo
     * @return $this
     */
    public function removeTransferTo(Transfer $transferTo)
    {
        $this->transferTo->removeWithdraws($transferTo);

        return $this;
    }

    public function __toString()
    {
        return $this->name;
    }

    /**
     * @return ArrayCollection|Sales[]
     */
    public function getSalesFrom()
    {
        return $this->salesFrom;
    }

    /**
     * @param ArrayCollection|Sales[] $salesFrom
     * @return Storage
     */
    public function setSalesFrom($salesFrom)
    {
        $this->salesFrom = $salesFrom;
        return $this;
    }

    /**
     * @param Sales $saleFrom
     * @return $this
     */
    public function addSalesFrom(Sales $saleFrom)
    {
        $this->salesFrom[] = $saleFrom;

        return $this;
    }

    /**
     * @param Sales $saleFrom
     * @return $this
     */
    public function removeSalesFrom(Sales $saleFrom)
    {
        $this->salesFrom->removeElement($saleFrom);

        return $this;
    }
}
