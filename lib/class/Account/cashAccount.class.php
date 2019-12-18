<?php

/**
 * Class cashAccount
 */
class cashAccount extends cashAbstractEntity
{
    use cashEntityBeforeSaveTrait;

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string|null
     */
    private $description;

    /**
     * @var string|null
     */
    private $icon;

    /**
     * @var string
     */
    private $currency;

    /**
     * @var float
     */
    private $currentBalance = 0.0;

    /**
     * @var int
     */
    private $customerContactId;

    /**
     * @var int
     */
    private $isArchived;

    /**
     * @var int
     */
    private $sort;

    /**
     * @var string|DateTime
     */
    private $createDatetime;

    /**
     * @var string|DateTime|null
     */
    private $updateDatetime;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return cashAccount
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return cashAccount
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     *
     * @return cashAccount
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @param string|null $icon
     *
     * @return cashAccount
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     *
     * @return cashAccount
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @return float
     */
    public function getCurrentBalance()
    {
        return $this->currentBalance;
    }

    /**
     * @param float $currentBalance
     *
     * @return cashAccount
     */
    public function setCurrentBalance($currentBalance)
    {
        $this->currentBalance = $currentBalance;

        return $this;
    }

    /**
     * @return int
     */
    public function getCustomerContactId()
    {
        return $this->customerContactId;
    }

    /**
     * @param int $customerContactId
     *
     * @return cashAccount
     */
    public function setCustomerContactId($customerContactId)
    {
        $this->customerContactId = $customerContactId;

        return $this;
    }

    /**
     * @return int
     */
    public function getIsArchived()
    {
        return $this->isArchived;
    }

    /**
     * @param int $isArchived
     *
     * @return cashAccount
     */
    public function setIsArchived($isArchived)
    {
        $this->isArchived = $isArchived;

        return $this;
    }

    /**
     * @return int
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * @param int $sort
     *
     * @return cashAccount
     */
    public function setSort($sort)
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * @return DateTime|string
     */
    public function getCreateDatetime()
    {
        return $this->createDatetime;
    }

    /**
     * @param DateTime|string $createDatetime
     *
     * @return cashAccount
     */
    public function setCreateDatetime($createDatetime)
    {
        $this->createDatetime = $createDatetime;

        return $this;
    }

    /**
     * @return DateTime|string|null
     */
    public function getUpdateDatetime()
    {
        return $this->updateDatetime;
    }

    /**
     * @param DateTime|string|null $updateDatetime
     *
     * @return cashAccount
     */
    public function setUpdateDatetime($updateDatetime)
    {
        $this->updateDatetime = $updateDatetime;

        return $this;
    }

    /**
     * @return bool
     */
    public function beforeSave()
    {
        $this->updateCreateUpdateDatetime();

        return true;
    }
}
