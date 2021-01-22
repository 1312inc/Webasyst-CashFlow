<?php

/**
 * Class cashApiShrinkTransactionResponseDto
 */
class cashApiShrinkTransactionResponseDto extends cashAbstractDto
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
     * @var int|null
     */
    public $is_onbadge;

    /**
     * cashApiTransactionResponse constructor.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->initializeWithArray($data);

        $this->id = (int) $this->id;
        $this->external_data = $this->external_data ? json_decode($this->external_data, true) : null;
        $this->amount = (float) $this->amount;
        $this->repeating_id = $this->repeating_id ? (int) $this->repeating_id : null;
        $this->contractor_contact_id = $this->contractor_contact_id ? (int) $this->contractor_contact_id : null;
        $this->create_contact_id = (int) $this->create_contact_id;
        $this->category_id = (int) $this->category_id;
        $this->account_id = (int) $this->account_id;
        $this->account_id = (int) $this->account_id;
        $this->is_archived = $this->is_archived ? true : false;
        $this->is_onbadge = $this->is_onbadge ? true : false;

        $this->planned = $this->date > date('Y-m-d');
        $this->amountShorten = cashShorteningService::money($this->amount);
    }
}
