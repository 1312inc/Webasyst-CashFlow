<?php

/**
 * Class cashTransaction
 */
class cashTransaction extends cashAbstractEntity
{
    use cashEntityBeforeSaveTrait;

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $date;

    /**
     * @var string
     */
    private $datetime;

    /**
     * @var int
     */
    private $accountId;

    /**
     * @var int|null
     */
    private $categoryId;

    /**
     * @var float
     */
    private $amount = 0.0;

    /**
     * @var string
     */
    private $description;

    /**
     * @var int|null
     */
    private $repeatingId;

    /**
     * @var int
     */
    private $createContactId;

    /**
     * @var string
     */
    private $createDatetime;

    /**
     * @var string|null
     */
    private $updateDatetime;

    /**
     * @var cashCategory|null
     */
    protected $category;

    /**
     * @var cashAccount
     */
    protected $account;

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
     * @return cashTransaction
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param string $date
     *
     * @return cashTransaction
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return string
     */
    public function getDatetime()
    {
        return $this->datetime;
    }

    /**
     * @param string $datetime
     *
     * @return cashTransaction
     */
    public function setDatetime($datetime)
    {
        $this->datetime = $datetime;

        return $this;
    }

    /**
     * @return int
     */
    public function getAccountId()
    {
        return $this->accountId;
    }

    /**
     * @param int $accountId
     *
     * @return cashTransaction
     */
    public function setAccountId($accountId)
    {
        $this->accountId = $accountId;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getCategoryId()
    {
        return $this->categoryId;
    }

    /**
     * @param int|null $categoryId
     *
     * @return cashTransaction
     */
    public function setCategoryId($categoryId)
    {
        $this->categoryId = $categoryId;

        return $this;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     *
     * @return cashTransaction
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return cashTransaction
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getRepeatingId()
    {
        return $this->repeatingId;
    }

    /**
     * @param int|null $repeatingId
     *
     * @return cashTransaction
     */
    public function setRepeatingId($repeatingId)
    {
        $this->repeatingId = $repeatingId;

        return $this;
    }

    /**
     * @return int
     */
    public function getCreateContactId()
    {
        return $this->createContactId;
    }

    /**
     * @param int $createContactId
     *
     * @return cashTransaction
     */
    public function setCreateContactId($createContactId)
    {
        $this->createContactId = $createContactId;

        return $this;
    }

    /**
     * @return string
     */
    public function getCreateDatetime()
    {
        return $this->createDatetime;
    }

    /**
     * @param string $createDatetime
     *
     * @return cashTransaction
     */
    public function setCreateDatetime($createDatetime)
    {
        $this->createDatetime = $createDatetime;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getUpdateDatetime()
    {
        return $this->updateDatetime;
    }

    /**
     * @param string|null $updateDatetime
     *
     * @return cashTransaction
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

    /**
     * @return cashCategory|null
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param cashCategory|null $category
     *
     * @return cashTransaction
     */
    public function setCategory(cashCategory $category = null)
    {
        if ($category) {
            $this->category = $category;
            $this->categoryId = $category->getId();
        } else {
            $this->category = null;
            $this->categoryId = null;
        }

        return $this;
    }

    /**
     * @return cashAccount
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * @param cashAccount $account
     *
     * @return cashTransaction
     */
    public function setAccount(cashAccount $account)
    {
        $this->account = $account;
        $this->accountId = $account->getId();

        return $this;
    }
}
