<?php

/**
 * Class cashCalculationService
 */
final class cashCalculationService
{
    public function getTransactionSummaryForAccounts()
    {
        /** @var cashTransactionModel $model */
        $model = cash()->getModel(cashTransaction::class);
    }
}
