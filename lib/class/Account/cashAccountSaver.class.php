<?php

/**
 * Class cashAccountSaver
 */
class cashAccountSaver extends cashEntitySaver
{
    /**
     * @param cashAccount $account
     * @param array $data
     * @param cashTransactionSaveParamsDto $params
     *
     * @return bool
     */
    public function saveFromArray($account, array $data, cashTransactionSaveParamsDto $params = null)
    {
        if (!$this->validate($data)) {
            return false;
        }

        try {
            unset($data['id']);

            if (!empty($data['icon_link']) && preg_match('~https?://.{2,225}\..{2,20}~', $data['icon_link'])) {
                $data['icon'] = $data['icon_link'];
                unset($data['icon_link']);
            }

            cash()->getHydrator()->hydrate($account, $data);
            cash()->getEntityPersister()->save($account);

            return true;
        } catch (Exception $ex) {
            $this->error = $ex->getMessage();
        }

        return false;
    }

    public function saveFromApi(
        cashAccount $account,
        cashApiAccountCreateRequest $request,
        cashTransactionSaveParamsDto $params = null
    ): bool {
        try {
            $account->setDescription($request->getDescription())
                ->setName($request->getName())
                ->setIcon($request->getIcon())
                ->setCurrency($request->getCurrency())
                ->setIsImaginary($request->getImaginary())
                ->setCustomerContactId(wa()->getUser()->getId());

            cash()->getEntityPersister()->save($account);

            return true;
        } catch (Exception $ex) {
            $this->error = $ex->getMessage();
        }

        return false;
    }

    /**
     * @param array $data
     *
     * @return bool
     */
    public function validate(array &$data)
    {
        if (!isset($data['name'])) {
            $this->error = _w('No account name');

            return false;
        }

        $data['name'] = trim($data['name']);
        if ($data['name'] === '') {
            $this->error = _w('Empty account name');

            return false;
        }

        if (empty($data['currency'])) {
            $this->error = _w('No account currency');

            return false;
        }

        return true;
    }

    /**
     * @param array $order
     *
     * @return bool
     */
    public function sort(array $order)
    {
        /** @var cashAccountRepository $rep */
        $rep = cash()->getEntityRepository(cashAccount::class);
        try {
            /** @var cashAccount[] $accounts */
            $accounts = $rep->findById($order);
            $order = array_flip($order);
            $i = 0;
            foreach ($accounts as $account) {
                if (!isset($order[$account->getId()])) {
                    continue;
                }

                $account->setSort($order[$account->getId()]);
                cash()->getEntityPersister()->update($account);
            }

            return true;
        } catch (Exception $exception) {
            $this->error = $exception->getMessage();
        }

        return false;
    }
}
