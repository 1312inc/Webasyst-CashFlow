<?php

/**
 * Class cashAccountSaveController
 */
class cashAccountSaveController extends cashJsonController
{
    /**
     * @throws Exception
     */
    public function execute()
    {
        $data = waRequest::post('account', [], waRequest::TYPE_ARRAY);

        $saver = new cashAccountSaver();
        $account = $saver->save($data);
        if ($account) {
            $accountDto = cashDtoFromEntityFactory::fromEntity(cashAccountDto::class, $account);
            $this->response = $accountDto;
        } else {
            $this->errors[] = $saver->getError();
        }
    }
}
