<?php

/**
 * Class cashApiTransactionUpdateRequest
 *
 * repeating_frequency
 */
class cashApiTransactionUpdateRequest extends cashApiTransactionCreateRequest
{
    /**
     * @var int
     */
    public $id = 0;

    /**
     * @var bool
     */
    public $apply_to_all_in_future = false;
}
