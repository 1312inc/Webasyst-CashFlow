<?php

/**
 * Class cashApiTransactionGetResponseDto
 */
class cashApiTransactionGetResponseDto extends cashApiTransactionResponseDto
{
    /**
     * @var int
     */
    public $occurrences_in_future = 0;

    /**
     * @var string
     */
    public $repeating_end_type;

    /**
     * @var string
     */
    public $repeating_interval;

    /**
     * @var array
     */
    public $repeating_end_conditions;

    /**
     * @var int
     */
    public $repeating_frequency;
}
