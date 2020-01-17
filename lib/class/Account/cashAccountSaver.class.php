<?php

/**
 * Class cashAccountSaver
 */
class cashAccountSaver extends cashEntitySaver
{
    /**
     * @param array $data
     *
     * @return bool|cashAccount
     */
    public function save(array $data)
    {
        if (!$this->validate($data)) {
            return false;
        }

        try {
            /** @var cashAccount $account */
            if (!empty($data['id'])) {
                $account = cash()->getEntityRepository(cashAccount::class)->findById($data['id']);
                kmwaAssert::instance($account, cashAccount::class);
                unset($data['id']);
            } else {
                $account = cash()->getEntityFactory(cashAccount::class)->createNew();
            }

            if (!empty($data['currency']) && $account->getId() && $data['currency'] !== $account->getCurrency()) {
                throw new kmwaNotImplementedException(_w('You can not change currency for existing account yet'));
            }

            cash()->getHydrator()->hydrate($account, $data);
            cash()->getEntityPersister()->save($account);

            return $account;
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
    public function validate(array $data)
    {
        if (empty($data['name'])) {
            $this->error = _w('No account name');

            return false;
        }

        if (empty($data['currency'])) {
            $this->error = _w('No account currency');

            return false;
        }

        return true;
    }
}
