<?php

/**
 * Class cashTransactionDto
 */
class cashApiTransactionDto extends cashAbstractDto
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $date;

    /**
     * @var string
     */
    public $datetime;

    /**
     * @var float
     */
    public $amount = 0.0;

    /**
     * @var string
     */
    public $amountShorten = '0';

    /**
     * @var float
     */
    public $balance = 0.0;

    /**
     * @var string
     */
    public $balanceShorten = '0';

    /**
     * @var string
     */
    public $currency;

    /**
     * @var string
     */
    public $description;

    /**
     * @var int|null
     */
    public $repeating_id;

    /**
     * @var int
     */
    public $create_contact_id;

    /**
     * @var string
     */
    public $create_datetime;

    /**
     * @var string|null
     */
    public $update_datetime;

    /**
     * @var int
     */
    public $category_id;

    /**
     * @var int
     */
    public $account_id;

    /**
     * @var bool
     */
    public $planned;

    /**
     * @var bool
     */
    public $is_archived;

    /**
     * @var string
     */
    public $external_hash;

    /**
     * @var string
     */
    public $external_source;

    /**
     * @var string
     */
    public $external_data;

    /**
     * @var int|null
     */
    public $contractor_contact_id;

    /**
     * cashApiTransactionDto constructor.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->initializeWithArray($data);

        $this->planned = $this->date > date('Y-m-d');
        $this->amountShorten = cashShorteningService::money($this->amount);
        $this->balanceShorten = cashShorteningService::money($this->balance);
    }
}
