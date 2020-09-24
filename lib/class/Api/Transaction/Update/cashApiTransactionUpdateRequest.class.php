<?php

/**
 * Class cashApiTransactionUpdateRequest
 *
 * repeating_frequency
 */
class cashApiTransactionUpdateRequest
{
    /**
     * @var int
     */
    public $id = 0;

    /**
     * @var bool
     */
    public $apply_to_all_in_future = false;

    /**
     * @var float
     */
    public $amount = 0.0;

    /**
     * @var string
     */
    public $date = '';

    /**
     * @var int
     */
    public $account_id = 0;

    /**
     * @var int
     */
    public $category_id = 0;

    /**
     * @var int
     */
    public $contractor_contact_id;

    /**
     * New contractor name
     *
     * @var string
     */
    public $contractor;

    /**
     * @var string
     */
    public $description;

    /**
     * @var int
     */
    public $repeating_frequency;

    /**
     * One of 'day', 'week', 'month', 'year'.
     *
     * @see cashRepeatingTransaction
     *
     * @var string
     */
    public $repeating_interval;

    /**
     * One of 'after', 'ondate'.
     *
     * @see cashRepeatingTransaction
     *
     * @var string
     */
    public $repeating_end_type;

    /**
     * @var int
     */
    public $repeating_end_after;

    /**
     * @var string
     */
    public $repeating_end_ondate;

    /**
     * @var int
     */
    public $transfer_account_id;

    /**
     * @var string
     */
    public $transfer_incoming_amount;
}
