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
    private $account_id;

    /**
     * @var int|null
     */
    private $category_id;

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
    private $repeating_id;

    /**
     * @var int
     */
    private $create_contact_id;

    /**
     * @var int|null
     */
    private $import_id;

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
        return $this->account_id;
    }

    /**
     * @param int $accountId
     *
     * @return cashTransaction
     */
    public function setAccountId($accountId)
    {
        $this->account_id = $accountId;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getCategoryId()
    {
        return $this->category_id;
    }

    /**
     * @param int|null $categoryId
     *
     * @return cashTransaction
     */
    public function setCategoryId($categoryId)
    {
        $this->category_id = $categoryId;

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
        return $this->repeating_id;
    }

    /**
     * @param int|null $repeatingId
     *
     * @return cashTransaction
     */
    public function setRepeatingId($repeatingId)
    {
        $this->repeating_id = $repeatingId;

        return $this;
    }

    /**
     * @return int
     */
    public function getCreateContactId()
    {
        return $this->create_contact_id;
    }

    /**
     * @param int $createContactId
     *
     * @return cashTransaction
     */
    public function setCreateContactId($createContactId)
    {
        $this->create_contact_id = $createContactId;

        return $this;
    }

    /**
     * @return bool
     */
    public function beforeSave()
    {
        $this->updateCreateUpdateDatetime();

        $this->setDatetime(date('Y-m-d H:i:s', strtotime($this->date)));

        return true;
    }

    /**
     * @return cashCategory
     */
    public function getCategory()
    {
        if ($this->category === null) {
            $this->category = cash()->getEntityRepository(cashCategory::class)->findById($this->category_id);
            if (!$this->category instanceof cashCategory) {
                $this->category = cash()->getEntityFactory(cashCategory::class)->createNewNoCategory();
            }
        }

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
            $this->category_id = $category->getId();
        } else {
            $this->category = null;
            $this->category_id = null;
        }

        return $this;
    }

    /**
     * @return cashAccount
     * @throws waException
     */
    public function getAccount()
    {
        if ($this->account === null) {
            $this->account = cash()->getEntityRepository(cashAccount::class)->findById($this->account_id);
        }

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
        $this->account_id = $account->getId();

        return $this;
    }

    /**
     * @return int|null
     */
    public function getImportId()
    {
        return $this->import_id;
    }

    /**
     * @param int $importId
     *
     * @return cashTransaction
     */
    public function setImportId($importId)
    {
        $this->import_id = $importId;

        return $this;
    }
}
