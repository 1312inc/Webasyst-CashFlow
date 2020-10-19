<?php

class cashTestCreateFixturesCli extends waCliController
{
    const BULK = 1000;

    public function run($params = null)
    {
        $limit = waRequest::param('limit', 10000, waRequest::TYPE_INT);
        $categories = cash()->getModel(cashCategory::class)->getAll();
        $accounts = cash()->getModel(cashAccount::class)->getAll();
        $model = cash()->getModel(cashTransaction::class);

        $start = strtotime("10 September 2010");
        $end = strtotime("22 July 2025");

        $inserts = $agContr = $agAcc = $agCats = $ag = [];

        $total = 0;
        while ($limit--) {
            $timestamp = mt_rand($start, $end);
            $category = $categories[array_rand($categories)];
            $account = $accounts[array_rand($accounts)];

            if ($category['type'] === 'transfer') {
                continue;
            }

            $insert = [
                'date' => date('Y-m-d', $timestamp),
                'datetime' => date('Y-m-d H:i:s', $timestamp),
                'account_id' => $account['id'],
                'category_id' => $category['id'],
                'amount' => (float) $category['type'] === 'expense' ? -mt_rand(1, 1000) : mt_rand(1, 1000),
                'description' => '',
                'repeating_id' => null,
                'create_contact_id' => 1,
                'create_datetime' => date('Y-m-d H:i:s'),
                'update_datetime' => date('Y-m-d H:i:s'),
                'import_id' => null,
                'is_archived' => 0,
                'external_hash' => null,
                'external_source' => null,
                'external_data' => null,
                'contractor_contact_id' => mt_rand(0, 100) ?: null,
            ];

            $agAcc[] = implode(
                ',',
                [
                    'date' => "'{$insert['date']}'",
                    'id' => (int) $account['id'],
                    'type' => "'{$category['type']}'",
                    'amount' => $insert['amount'],
                ]
            );

            $agCats[] = implode(
                ',',
                [
                    'date' => "'{$insert['date']}'",
                    'id' => (int) $category['id'],
                    'type' => "'{$category['type']}'",
                    'amount' => $insert['amount'],
                ]
            );

            $ag[] = implode(
                ',',
                [
                    'date' => "'{$insert['date']}'",
                    'account_id' => (int) $account['id'],
                    'category_id' => (int) $category['id'],
                    'contractor_contact_id' => $insert['contractor_contact_id'] ?? 0,
                    'type' => "'{$category['type']}'",
                    'amount' => $insert['amount'],
                ]
            );

            if ($insert['contractor_contact_id']) {
                $agContr[] = implode(
                    ',',
                    [
                        'date' => "'{$insert['date']}'",
                        'id' => $insert['contractor_contact_id'],
                        'type' => "'{$category['type']}'",
                        'amount' => $insert['amount'],
                    ]
                );
            }

            $inserts[] = $insert;
            if ($limit % self::BULK === 0) {
                $model->startTransaction();
                $model->multipleInsert($inserts);

                $model->query(
                    sprintf(
                        'insert into cash_aggregate_category (date, aggregate_id, type, amount) values (%s) on duplicate key update amount = amount + values(amount)',
                        implode('),(', $agCats)
                    )
                );
                $model->query(
                    sprintf(
                        'insert into cash_aggregate_account (date, aggregate_id, type, amount) values (%s) on duplicate key update amount = amount + values(amount)',
                        implode('),(', $agAcc)
                    )
                );
                $model->query(
                    sprintf(
                        'insert into cash_aggregate_contractor (date, aggregate_id, type, amount) values (%s) on duplicate key update amount = amount + values(amount)',
                        implode('),(', $agContr)
                    )
                );
                $model->query(
                    sprintf(
                        'insert into cash_aggregate (date, account_id, category_id, contractor_contact_id, type, amount) values (%s) on duplicate key update amount = amount + values(amount)',
                        implode('),(', $ag)
                    )
                );
                $model->commit();

                $inserts = $agContr = $agAcc = $agCats = $ag = [];

                $total += self::BULK;
                echo date('Y-m-d H:i:s') . "\tsaved {$total}\n";
            }
        }
    }
}
