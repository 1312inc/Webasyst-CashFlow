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
SELECT MAX(ct.id) last_transaction_id,
       ct.date    last_transaction_date,
       ct.amount  last_transaction_amount,
       ca.currency last_transaction_currency,
       ct.contractor_contact_id contact_id
FROM cash_transaction ct
         INNER JOIN (
    SELECT ct2.contractor_contact_id, MAX(ct2.date) AS date
    FROM cash_transaction ct2
             JOIN cash_account ca ON ct2.account_id = ca.id
    WHERE ca.is_archived = 0
      AND ct2.is_archived = 0
      AND ct2.contractor_contact_id IS NOT NULL
      AND ct2.category_id != i:transfer_id
      AND ct2.date <= s:date
    GROUP BY ct2.contractor_contact_id
) ct2 ON ct2.contractor_contact_id = ct.contractor_contact_id AND ct2.date = ct.date
         JOIN wa_contact wc ON ct.contractor_contact_id = wc.id
         JOIN cash_account ca ON ct.account_id = ca.id
GROUP BY ct.contractor_contact_id, ct.date
ORDER BY ct.date DESC, ct.contractor_contact_id
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
                'last_transaction_amount' => (float) $contactData['last_transaction_amount'],
                'last_transaction_currency' => $contactData['last_transaction_currency'],
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