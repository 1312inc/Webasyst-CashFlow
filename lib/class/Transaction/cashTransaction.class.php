<?php

/**
 * Class cashTransaction
 */
class cashTransaction extends cashAbstractEntity
{
    use kmwaEntityDatetimeTrait;
    use cashEntityJsonTransformerTrait;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $date;

    /**
     * @var string
     */
    protected $datetime;

    /**
     * @var int
     */
    protected $account_id;

    /**
     * @var int|null
     */
    protected $category_id;

    /**
     * @var float
     */
    protected $amount = 0.0;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var int|null
     */
    private $repeating_id;

    /**
     * @var int
     */
    protected $create_contact_id;

    /**
     * @var int|null
     */
    private $import_id;

    /**
     * @var bool|int
     */
    private $is_archived = 0;

    /**
     * @var null|string
     */
    protected $external_hash = null;

    /**
     * @var null|string
     */
    protected $external_source = null;

    /**
     * @var null|array
     */
    protected $external_data = null;

    /**
     * @var cashCategory|null
     */
    protected $category;

    /**
     * @var cashAccount
     */
    protected $account;

    /**
     * @var cashRepeatingTransaction
     */
    private $repeatingTransaction;

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

        if (!$this->category_id) {
            $this->category_id = null;
            $this->category = null;
        }

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
            if ($this->category_id) {
                $this->category = cash()->getEntityRepository(cashCategory::class)->findById($this->category_id);
            }
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

    /**
     * @return bool|int
     */
    public function getIsArchived()
    {
        return $this->is_archived;
    }

    /**
     * @param bool|int $is_archived
     *
     * @return cashTransaction
     */
    public function setIsArchived($is_archived)
    {
        $this->is_archived = $is_archived;

        return $this;
    }

    /**
     * @return cashRepeatingTransaction|null
     * @throws waException
     */
    public function getRepeatingTransaction()
    {
        if ($this->repeatingTransaction === null && $this->repeating_id) {
            $this->repeatingTransaction = cash()->getEntityRepository(cashRepeatingTransaction::class)->findById(
                $this->repeating_id
            );
        }

        return $this->repeatingTransaction;
    }

    /**
     * @param cashRepeatingTransaction $repeatingTransaction
     *
     * @return cashTransaction
     */
    public function setRepeatingTransaction(cashRepeatingTransaction $repeatingTransaction)
    {
        $this->repeatingTransaction = $repeatingTransaction;
        $this->repeating_id = $repeatingTransaction->getId();

        return $this;
    }

    /**
     * @return string|null
     */
    public function getExternalHash()
    {
        return $this->external_hash;
    }

    /**
     * @param string|null $external_hash
     *
     * @return cashTransaction
     */
    public function setExternalHash($external_hash)
    {
        $this->external_hash = $external_hash;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getExternalSource()
    {
        return $this->external_source;
    }

    /**
     * @param string|null $external_source
     *
     * @return cashTransaction
     */
    public function setExternalSource($external_source)
    {
        $this->external_source = $external_source;

        return $this;
    }

    public function beforeExtract(array &$fields)
    {
        $this->toJson(['external_data']);
    }

    public function afterExtract(array &$fields)
    {
        $this->fromJson(['external_data']);
    }

    public function afterHydrate($data = [])
    {
        $this->fromJson(['external_data']);
    }

    /**
     * @return array|null
     */
    public function getExternalData()
    {
        return $this->external_data;
    }

    /**
     * @param array|null $external_data
     *
     * @return cashTransaction
     */
    public function setExternalData($external_data = null)
    {
        $this->external_data = $external_data;

        return $this;
    }
}
