<?php

/**
 * Class cashRepeatingTransactionSettingsDto
 */
class cashRepeatingTransactionSettingsDto
{
    use cashCreateFromArrayTrait;

    /**
     * @var int|bool
     */
    public $apply_to_all_in_future;

    /**
     * @var int
     */
    public $frequency = cashRepeatingTransaction::DEFAULT_REPEATING_FREQUENCY;

    /**
     * @var string
     */
    public $interval = cashRepeatingTransaction::INTERVAL_NONE;

    /**
     * @var string
     */
    public $end_type = cashRepeatingTransaction::REPEATING_END_NEVER;

    /**
     * @var array
     */
    public $end = [
        'after' => 0,
        'ondate' => '',
    ];

    /**
     * @var array
     */
    public $transfer = [];

    /**
     * cashRepeatingTransactionSettingsDto constructor.
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        if ($data) {
            $this->initializeWithArray($data);
        }
    }
}
