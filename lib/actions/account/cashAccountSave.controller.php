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
        /** @var cashAccount $account */
        if (!empty($data['id'])) {
            $account = cash()->getEntityRepository(cashAccount::class)->findById($data['id']);
            kmwaAssert::instance($account, cashAccount::class);
        } else {
            $account = cash()->getEntityFactory(cashAccount::class)->createNew();
        }

        if ($saver->saveFromArray($account, $data)) {
            $accountDto = cashDtoFromEntityFactory::fromEntity(cashAccountDto::class, $account);
            $this->response = $accountDto;
        } else {
            $this->errors[] = $saver->getError();
        }
    }
}
