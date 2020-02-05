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
    private $current_balance = 0.0;

    /**
     * @var int
     */
    private $customer_contact_id;

    /**
     * @var int
     */
    private $is_archived;

    /**
     * @var int
     */
    private $sort;

    /**
     * @var string|DateTime
     */
    private $create_datetime;

    /**
     * @var string|DateTime|null
     */
    private $update_datetime;

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
        return $this->current_balance;
    }

    /**
     * @param float $currentBalance
     *
     * @return cashAccount
     */
    public function setCurrentBalance($currentBalance)
    {
        $this->current_balance = $currentBalance;

        return $this;
    }

    /**
     * @return int
     */
    public function getCustomerContactId()
    {
        return $this->customer_contact_id;
    }

    /**
     * @param int $customerContactId
     *
     * @return cashAccount
     */
    public function setCustomerContactId($customerContactId)
    {
        $this->customer_contact_id = $customerContactId;

        return $this;
    }

    /**
     * @return int
     */
    public function getIsArchived()
    {
        return $this->is_archived;
    }

    /**
     * @param int $isArchived
     *
     * @return cashAccount
     */
    public function setIsArchived($isArchived)
    {
        $this->is_archived = $isArchived;

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
        return $this->create_datetime;
    }

    /**
     * @param DateTime|string $createDatetime
     *
     * @return cashAccount
     */
    public function setCreateDatetime($createDatetime)
    {
        $this->create_datetime = $createDatetime;

        return $this;
    }

    /**
     * @return DateTime|string|null
     */
    public function getUpdateDatetime()
    {
        return $this->update_datetime;
    }

    /**
     * @param DateTime|string|null $updateDatetime
     *
     * @return cashAccount
     */
    public function setUpdateDatetime($updateDatetime)
    {
        $this->update_datetime = $updateDatetime;

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
