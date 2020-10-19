<?php

class cashTestCreateAggregateFixturesCli extends waCliController
{
    public function run($params = null)
    {
        $limit = waRequest::param('limit', 10000, waRequest::TYPE_INT);
        $categories = cash()->getModel(cashCategory::class)->getAll();
        $accounts = cash()->getModel(cashAccount::class)->getAll();
        /** @var cashModel $model */
        $model = cash()->getModel();

        $start = strtotime("10 September 2000");
        $end = strtotime("22 July 2030");

        $total = 0;
        while ($limit--) {
            $timestamp = mt_rand($start, $end);
            $category = $categories[array_rand($categories)];

            if ($category['type'] === 'transfer') {
                continue;
            }

            $model->query(
                'insert into cash_aggregate_category (date, aggregate_id, type, amount) values (s:date, i:id, s:type, f:amount) on duplicate key update amount = values(amount) + f:amount',
                [
                    'date' => date('Y-m-d', $timestamp),
                    'id' => $category['id'],
                    'type' => $category['type'],
                    'amount' => $category['type'] === 'expense' ? -mt_rand(1, 1000) : mt_rand(1, 1000),
                ]
            );

            if ($limit % 1000 === 0) {
                $total += 1000;
                echo "saved category {$total}\n";
            }
        }

        $limit = waRequest::param('limit', 10000, waRequest::TYPE_INT);
        $total = 0;
        while ($limit--) {
            $timestamp = mt_rand($start, $end);
            $account = $accounts[array_rand($accounts)];
            $category = $categories[array_rand($categories)];

            if ($category['type'] === 'transfer') {
                continue;
            }

            $model->query(
                'insert into cash_aggregate_account (date, aggregate_id, type, amount) values (s:date, i:id, s:type, f:amount) on duplicate key update amount = value(amount) + f:amount',
                [
                    'date' => date('Y-m-d', $timestamp),
                    'id' => $account['id'],
                    'type' => $category['type'],
                    'amount' => $category['type'] === 'expense' ? -mt_rand(1, 1000) : mt_rand(1, 1000),
                ]
            );

            if ($limit % 1000 === 0) {
                $total += 1000;
                echo "saved account {$total}\n";
            }
        }

        $limit = waRequest::param('limit', 10000, waRequest::TYPE_INT);
        $total = 0;
        while ($limit--) {
            $timestamp = mt_rand($start, $end);
            $category = $categories[array_rand($categories)];

            if ($category['type'] === 'transfer') {
                continue;
            }

            $model->query(
                'insert into cash_aggregate_contractor (date, aggregate_id, type, amount) values (s:date, i:id, s:type, f:amount) on duplicate key update amount = values(amount) + f:amount',
                [
                    'date' => date('Y-m-d', $timestamp),
                    'id' => rand(1, 100),
                    'type' => $category['type'],
                    'amount' => $category['type'] === 'expense' ? -mt_rand(1, 1000) : mt_rand(1, 1000),
                ]
            );

            if ($limit % 1000 === 0) {
                $total += 1000;
                echo "saved contractor {$total}\n";
            }
        }
    }
}
