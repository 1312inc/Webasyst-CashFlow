<?php

/**
 * Class cashCsvImportSettings
 */
class cashCsvImportSettings implements JsonSerializable
{
    const TYPE_SINGLE = 1;
    const TYPE_MULTI  = 2;

    /**
     * @var int
     */
    private $accountType = self::TYPE_SINGLE;

    /**
     * @var string
     */
    private $account;

    /**
     * @var array
     */
    private $accountMap = [];

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $datetime;

    /**
     * @var int
     */
    private $amountType = self::TYPE_SINGLE;

    /**
     * @var string
     */
    private $amount;

    /**
     * @var array
     */
    private $amountMap = [];

    /**
     * @var int
     */
    private $categoryType = self::TYPE_SINGLE;

    /**
     * @var string
     */
    private $categoryIncome;

    /**
     * @var string
     */
    private $categoryExpense;

    /**
     * @var array
     */
    private $categoryMap = [];

    /**
     * @var string|null
     */
    private $category;

    /**
     * @var string
     */
    private $error;

    /**
     * @var bool
     */
    private $skipDuplicates = true;

    /**
     * cashCsvImportSettingsDto constructor.
     *
     * @param array $request
     */
    public function __construct(array $request)
    {
        $this->description = ifset($request['description']);
        $this->datetime = ifset($request['datetime']);

        $this->accountType = ifset($request['account']['type'], self::TYPE_SINGLE);
        $this->accountMap = ifset($request['account']['multi']['map'], []);
        $this->account = $this->accountType == self::TYPE_SINGLE
            ? ifset($request['account']['single']['column'])
            : ifset($request['account']['multi']['column']);

        $this->amountType = ifset($request['amount']['type'], self::TYPE_SINGLE);
        $this->amountMap = ifset($request['amount']['multi']['map'], []);
        if ($this->amountType == self::TYPE_SINGLE) {
            $this->amount = ifset($request['amount']['single']['column']);
        }

        $this->categoryType = ifset($request['category']['type'], self::TYPE_SINGLE);
        $this->categoryMap = ifset($request['category']['multi']['map'], []);
        $this->category = ifset($request['category']['multi']['column']);
        if ($this->accountType == self::TYPE_SINGLE) {
            $this->categoryIncome = ifset($request['category']['single']['income']['column']);
            $this->categoryExpense = ifset($request['category']['single']['expense']['column']);
        }

        $this->skipDuplicates = ifset($request['options']['skip_duplicates'], true);
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        if (empty($this->amount) && (empty(array_filter($this->amountMap)))) {
            $this->error = _w('No amount column');
            return false;
        }

        if ($this->accountType == self::TYPE_SINGLE && empty($this->account)) {
            $this->error = _w('No account column');
            return false;
        }

        if ($this->accountType == self::TYPE_MULTI && (empty($this->accountMap) || empty($this->account))) {
            $this->error = _w('No account column');
            return false;
        }

        if (empty($this->datetime)) {
            $this->error = _w('No date column');
            return false;
        }

        return true;
    }

    /**
     * @return int
     */
    public function getAccountType()
    {
        return $this->accountType;
    }

    /**
     * @return string
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * @return array
     */
    public function getAccountMap()
    {
        return $this->accountMap;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getDatetime()
    {
        return $this->datetime;
    }

    /**
     * @return int
     */
    public function getAmountType()
    {
        return $this->amountType;
    }

    /**
     * @return string
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return array
     */
    public function getAmountMap()
    {
        return $this->amountMap;
    }

    /**
     * @return string
     */
    public function getIncome()
    {
        return $this->amountMap['income'];
    }

    /**
     * @return string
     */
    public function getExpense()
    {
        return $this->amountMap['expense'];
    }

    /**
     * @return int
     */
    public function getCategoryType()
    {
        return $this->categoryType;
    }

    /**
     * @return string
     */
    public function getCategoryIncome()
    {
        return $this->categoryIncome;
    }

    /**
     * @return string
     */
    public function getCategoryExpense()
    {
        return $this->categoryExpense;
    }

    /**
     * @return array
     */
    public function getCategoryMap()
    {
        return $this->categoryMap;
    }

    /**
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @return string|null
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @return bool
     */
    public function isSkipDuplicates()
    {
        return $this->skipDuplicates;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
