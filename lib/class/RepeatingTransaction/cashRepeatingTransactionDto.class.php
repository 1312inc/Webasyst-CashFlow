<?php

/**
 * Class cashRepeatingTransactionDto
 */
class cashRepeatingTransactionDto extends cashTransactionDto
{
    /**
     * @var int
     */
    public $account_id;

    /**
     * @var int|null
     */
    public $category_id;

    /**
     * @var int
     */
    public $create_contact_id;

    /**
     * @var int
     */
    public $enabled = 1;

    /**
     * @var int
     */
    public $repeating_interval = cashRepeatingTransaction::DEFAULT_REPEATING_FREQUENCY;

    /**
     * @var string
     */
    public $repeating_frequency = cashRepeatingTransaction::INTERVAL_DAY;

    /**
     * @var array|string
     */
    public $repeating_conditions = [];

    /**
     * @var array|string
     */
    public $repeating_end_conditions = [
        'type' => cashRepeatingTransaction::REPEATING_END_NEVER,
        'after' => 0,
        'ondate' => '',
    ];

    /**
     * @var int
     */
    public $repeating_occurrences = 0;

    /**
     * @var int
     */
    public $occurrences_in_future = 0;

    /**
     * cashRepeatingTransactionDto constructor.
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
        parent::__construct($data, $account, $currency, $category);

        if (!is_array($this->repeating_end_conditions)) {
            $this->repeating_end_conditions = json_decode($this->repeating_end_conditions, true);
        }
        if (!is_array($this->repeating_conditions)) {
            $this->repeating_conditions = json_decode($this->repeating_conditions, true);
        }
    }
}
