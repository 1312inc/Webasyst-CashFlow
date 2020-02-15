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
    public function saveFromArray(array $data)
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

            if (!empty($data['icon_link']) && preg_match('~https?://.{2,225}\..{2,20}~', $data['icon_link'])) {
                $data['icon'] = $data['icon_link'];
                unset($data['icon_link']);
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

    /**
     * @param array $order
     *
     * @return bool
     */
    public function sort(array $order)
    {
        try {
            $accounts = cash()->getModel(cashAccount::class)
                ->select('*')
                ->where('id in (i:ids)', ['ids' => $order])
                ->fetchAll('id');
            $i = 0;
            foreach ($order as $accountId) {
                if (!isset($accounts[$accountId])) {
                    continue;
                }

                $accounts[$accountId]['sort'] = $i++;
                $this->saveFromArray($accounts[$accountId]);
            }

            return true;
        } catch (Exception $exception) {
            $this->error = $exception->getMessage();
        }

        return false;
    }
}
