<?php

/**
 * Class cashApiTransactionResponse
 */
class cashApiTransactionResponseDto extends cashAbstractDto
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
    public $description;

    /**
     * @var int|null
     */
    public $repeating_id;

    /**
     * @var int|null
     */
    public $repeating_data;

    /**
     * @var int
     */
    public $create_contact_id;

    /**
     * @var null|array
     */
    public $create_contact = null;

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
     * @var array
     */
    public $external_source_info;

    /**
     * @var string|null
     */
    public $external_source;

    /**
     * @var int|null
     */
    public $contractor_contact_id;

    /**
     * @var int|null
     */
    public $is_onbadge;

    /**
     * @var int|null
     */
    public $is_self_destruct_when_due;

    /**
     * @var array|null
     */
    public $contractor_contact = null;

    /**
     * @var int
     */
    public $affected_transactions = 0;

    /**
     * @var array<int>|null
     */
    public $affected_transaction_ids;

    /**
     * @var array
     */
    private $data;

    /**
     * cashApiTransactionResponse constructor.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->initializeWithArray($data);

        $this->data = $data;

        $this->id = (int) $this->id;
        $this->amount = (float) $this->amount;
        $this->balance = (float) $this->balance;
        $this->repeating_id = $this->repeating_id ? (int) $this->repeating_id : null;
        $this->contractor_contact_id = $this->contractor_contact_id ? (int) $this->contractor_contact_id : null;
        $this->create_contact_id = (int) $this->create_contact_id;
        $this->category_id = (int) $this->category_id;
        $this->account_id = (int) $this->account_id;
        $this->account_id = (int) $this->account_id;
        $this->is_archived = (bool) $this->is_archived;
        $this->is_onbadge = (bool) $this->is_onbadge;
        $this->is_onbadge = (bool) $this->is_onbadge;
        $this->is_self_destruct_when_due = (bool) $this->is_self_destruct_when_due;

        $this->planned = $this->date > date('Y-m-d');
        $this->amountShorten = cashShorteningService::money($this->amount);
        $this->balanceShorten = cashShorteningService::money($this->balance);
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }
}
