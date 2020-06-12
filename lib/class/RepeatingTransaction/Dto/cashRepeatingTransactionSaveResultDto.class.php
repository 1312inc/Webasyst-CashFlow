<?php

/**
 * Class cashRepeatingTransactionSaveResultDto
 */
class cashRepeatingTransactionSaveResultDto
{
    /**
     * @var cashRepeatingTransaction
     */
    public $oldTransaction;

    /**
     * @var cashRepeatingTransaction
     */
    public $newTransaction;

    /**
     * @var bool
     */
    public $shouldRepeat = false;

    /**
     * @var bool
     */
    public $ok = false;

    /**
     * @var string
     */
    public $error = '';
}
