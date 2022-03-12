<?php

final class cashQueryGetContractors
{
    /**
     * @var cashTransactionModel
     */
    private $model;

    public function __construct()
    {
        $this->model = cash()->getModel(cashTransaction::class);
    }

    public function getContractors(int $offset, int $limit, waContact $contact): array
    {
        $sql = <<<SQL
SELECT ct.contractor_contact_id contact_id,
       MAX(ct.date)             last_transaction_date
FROM cash_transaction ct
        JOIN cash_account ca ON ct.account_id = ca.id
        JOIN wa_contact wc ON ct.contractor_contact_id = wc.id
WHERE ca.is_archived = 0
    AND ct.is_archived = 0
    AND ct.contractor_contact_id IS NOT NULL
    AND ct.category_id != i:transfer_id
    AND ct.date <= s:date
GROUP BY ct.contractor_contact_id
ORDER BY last_transaction_date DESC, ct.contractor_contact_id
LIMIT i:offset, i:limit
SQL;

        $contacts = $this->model
            ->query(
                $sql,
                [
                    'limit' => $limit,
                    'offset' => $offset,
                    'date' => date('Y-m-d'),
                    'transfer_id' => cashCategoryFactory::TRANSFER_CATEGORY_ID,
                ]
            )
            ->fetchAll();
        $contactIds = array_column($contacts, 'contact_id');
        $result = [];
        $stat = $this->getStatDataForContractors($contact, $contactIds);
        foreach ($contacts as $contactData) {
            $r = [
                'contact_id' => (int) $contactData['contact_id'],
                'last_transaction_date' => $contactData['last_transaction_date'],
                'stat' => [],
            ];

            if (isset($stat[$contactData['contact_id']])) {
                $r['stat'] = array_reduce($stat[$contactData['contact_id']], static function ($carry, array $statData) {
                    $carry[] = [
                        'currency' => $statData['currency'],
                        'stat' => [
                            'income' => (float) $statData['income'],
                            'expense' => (float) $statData['expense'],
                            'summary' => (float) $statData['summary'],
                        ],
                    ];

                    return $carry;
                }, []);
            }

            $result[] = $r;
        }

        return $result;
    }

    public function getTotalContractors(waContact $contact): int
    {
        $accountTransactionRights = cash()->getContactRights()->getSqlForFilterTransactionsByAccount($contact);
//        $accountRights = cash()->getContactRights()->getSqlForAccountJoinWithFullAccess($contact);

        $sql = <<<SQL
SELECT COUNT(DISTINCT ct.contractor_contact_id)
FROM cash_transaction ct
         JOIN cash_account ca ON ct.account_id = ca.id
         JOIN wa_contact wc ON ct.contractor_contact_id = wc.id
WHERE ca.is_archived = 0
  AND ct.is_archived = 0
  AND ct.category_id != i:transfer_id
  AND ct.contractor_contact_id IS NOT NULL
  AND ct.date <= s:date
  AND {$accountTransactionRights}
SQL;

        return (int) $this->model
            ->query($sql, ['transfer_id' => cashCategoryFactory::TRANSFER_CATEGORY_ID, 'date' => date('Y-m-d')])
            ->fetchField();
    }

    private function getStatDataForContractors(waContact $contact, array $contractorIds = []): array
    {
        $contractorFilterSql = '';
        if ($contractorIds) {
            $contractorFilterSql = ' AND ct.contractor_contact_id IN (i:contractor_ids)';
        }

        $accountTransactionRights = cash()->getContactRights()->getSqlForFilterTransactionsByAccount($contact);
//        $accountRights = cash()->getContactRights()->getSqlForAccountJoinWithFullAccess($contact);

        $sql = <<<SQL
SELECT ct.contractor_contact_id             contractor_id,
       ca.currency,
       sum(if(ct.amount > 0, ct.amount, 0)) income,
       sum(if(ct.amount < 0, ct.amount, 0)) expense,
       sum(ct.amount)                       summary
FROM cash_transaction ct
         JOIN cash_account ca ON ct.account_id = ca.id
         JOIN wa_contact wc ON ct.contractor_contact_id = wc.id
WHERE ca.is_archived = 0
  AND ct.is_archived = 0
  AND ct.category_id != i:transfer_id
  AND ct.contractor_contact_id IS NOT NULL
  AND ct.date <= s:date
  AND {$accountTransactionRights}
  {$contractorFilterSql}
GROUP BY ct.contractor_contact_id, ca.currency
SQL;

        return $this->model
            ->query(
                $sql,
                [
                    'contractor_ids' => $contractorIds,
                    'transfer_id' => cashCategoryFactory::TRANSFER_CATEGORY_ID,
                    'date' => date('Y-m-d'),
                ]
            )
            ->fetchAll('contractor_id', 2);
    }
}