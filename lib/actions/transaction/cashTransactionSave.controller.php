<?php

/**
 * Class cashTransactionSaveController
 */
class cashTransactionSaveController extends cashJsonController
{
    /**
     * @throws waException
     * @throws Exception
     */
    public function execute()
    {
        $data = waRequest::post('transaction', [], waRequest::TYPE_ARRAY);

        $saver = new cashTransactionSaver();
        $transaction = $saver->save($data);
        if ($transaction) {
            $transactionDto = (new cashTransactionDtoAssembler())->createFromEntity($transaction);
            $this->response = $transactionDto;
        } else {
            $this->errors[] = $saver->getError();
        }
    }
}
