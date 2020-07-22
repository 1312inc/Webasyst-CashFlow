<?php

/**
 * Class cashTransactionDto
 */
class cashTransactionDto extends cashAbstractDto
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
     * @var cashCurrencyVO
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
     * @var cashCategoryDto
     */
    public $category;

    /**
     * @var cashAccountDto
     */
    public $account;

    /**
     * @var bool
     */
    public $planned;

    /**
     * @var bool
     */
    public $is_archived;

    /**
     * @var cashTransactionExternalEntityInterface|null
     */
    public $external_entity;

    /**
     * @var waContact
     */
    public $contractor;

    /**
     * cashTransactionDto constructor.
     *
     * @param array                $data
     * @param cashAccountDto|null  $account
     * @param cashCurrencyVO|null  $currency
     * @param cashCategoryDto|null $category
     */
    public function __construct(
        array $data,
        cashAccountDto $account = null,
        cashCurrencyVO $currency = null,
        cashCategoryDto $category = null
    ) {
        $this->initializeWithArray($data);

        $this->planned = $this->date > date('Y-m-d');
        $this->amountShorten = cashShorteningService::money($this->amount);
        $this->balanceShorten = cashShorteningService::money($this->balance);

        $this->account = $account;
        $this->currency = $currency;
        $this->category = $category;

        $this->contractor = new waContact(
            !empty($data['contractor_contact_id']) ? $data['contractor_contact_id'] : null
        );
    }
}
