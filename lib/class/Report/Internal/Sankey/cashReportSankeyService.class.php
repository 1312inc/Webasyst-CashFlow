<?php

final class cashReportSankeyService
{
    /**
     * @var cashModel
     */
    private $model;

    public function __construct()
    {
        $this->model = cash()->getModel();
    }

    public function getDataForPeriod(DateTimeImmutable $dateFrom, DateTimeImmutable $dateTo): array
    {
        $sql = <<<SQL
(SELECT ca.currency, cc.name 'from', cc.id 'from_id', ca.name 'to', ca.id 'to_id', cc.color color, SUM(ABS(ct.amount)) value, 'income' direction
FROM cash_transaction ct
         JOIN cash_category cc on ct.category_id = cc.id
         JOIN cash_account ca on ca.id = ct.account_id
WHERE ct.is_archived = 0
  AND ca.is_archived = 0
  AND cc.type = s:category_type_income
  AND ct.date >= s:date_from
  AND ct.date <= s:date_to
GROUP BY ct.category_id, ct.account_id)
UNION ALL
(SELECT ca.currency, ca.name 'from', ca.id 'from_id', cc.name 'to', cc.id 'to_id', cc.color color, SUM(ABS(ct.amount)) value, 'expense' direction
FROM cash_transaction ct
         JOIN cash_category cc on ct.category_id = cc.id
         JOIN cash_account ca on ca.id = ct.account_id
WHERE ct.is_archived = 0
  AND ca.is_archived = 0
  AND cc.type = s:category_type_expense
  AND ct.date >= s:date_from
  AND ct.date <= s:date_to
GROUP BY ct.account_id, ct.category_id)
SQL;

        $data = $this->model->query(
            $sql,
            [
                'category_type_income' => cashCategory::TYPE_INCOME,
                'category_type_expense' => cashCategory::TYPE_EXPENSE,
                'date_from' => $dateFrom->format('Y-m-d'),
                'date_to' => $dateTo->format('Y-m-d'),
            ]
        )->fetchAll();

        // разбили по валютам
        $chartData = array_reduce($data, static function ($carry, $datum) {
            $currency = $datum['currency'];
            if (!isset($carry[$currency])) {
                $carry[$currency] = [
                    'details' => cashCurrencyVO::fromWaCurrency($currency),
                    'data' => [],
                ];
            }

            unset($datum['currency']);
            $datum['value'] = (float) $datum['value'];
            $carry[$currency]['data'][] = $datum;

            return $carry;
        }, []);

        foreach ($chartData as $currency => &$datum) {
            $grouping = [
                'income' => [
                    'total' => 0,
                    'lines' => [],
                    'group' => [],
                ],
                'expense' => [
                    'total' => 0,
                    'lines' => [],
                    'group' => [],
                ],
            ];

            $datumData = &$datum['data'];
            usort($datumData, static function (array $item1, array $item2) {
                return $item1['value'] > $item2['value'];
            });

            array_walk($datumData, static function (&$item) use ($datum) {
                $item['currency'] = $datum['details']->getCode();
                $item['currencySign'] = $datum['details']->getSign();
            });

            // разделили на expense/income, посчитали тотал для типа
            $byType = array_reduce(
                $datumData,
                static function (array $carry, array $line) use (&$grouping) {
                    $grouping[$line['direction']]['total'] += $line['value'];
                    $carry[$line['direction']][] = $line;

                    return $carry;
                },
                ['income' => [], 'expense' => []]
            );

            // посчитаем вклад каждой "линии", выделим группы с суммой меньше 2%
            foreach ($byType as $direction => $lines) {
                $groupingKey = $direction === 'expense' ? 'from_id' : 'to_id';

                foreach ($lines as $line) {
                    $percentFromTotal = $line['value'] * 100 / $grouping[$direction]['total'];

                    // пробуем группировать только "тонкие"
                    if ($percentFromTotal < 2) {
                        if (!isset($grouping[$direction]['group'][$line[$groupingKey]])) {
                            $grouping[$direction]['group'][$line[$groupingKey]] = [
                                'percent' => 0,
                                'lines' => [],
                            ];
                        }

                        if ($grouping[$direction]['group'][$line[$groupingKey]]['percent'] + $percentFromTotal < 2) {
                            $grouping[$direction]['group'][$line[$groupingKey]]['percent'] += $percentFromTotal;
                            $grouping[$direction]['group'][$line[$groupingKey]]['lines'][] = $line;
                        }
                    } else {
                        $grouping[$direction]['lines'][] = $line;
                    }
                }

                $byType[$direction] = [];
                // что-то получилось сгруппировать
                if (!empty($grouping[$direction]['group'])) {
                    foreach ($grouping[$direction]['group'] as $group) {
                        // если линяя только одна - перенесем как есть
                        if (count($group['lines']) === 1) {
                            $byType[$direction][] = $group['lines'][0];
                        } else {
                            // иначе сгруппируем
                            $grouped = [
                                'value' => array_reduce($group['lines'], static function ($sum, array $i) {
                                    $sum += $i['value'];

                                    return $sum;
                                }, 0),
                                'direction' => $direction,
                                'color' => $group['lines'][0]['color'],
                                'currency' => $group['lines'][0]['currency'],
                                'currencySign' => $group['lines'][0]['currencySign'],
                            ];
                            if ($direction === 'income') {
                                $grouped['from'] = sprintf_wp('Other (%s)', $direction);
                                $grouped['from_id'] =0;
                                $grouped['to'] = $group['lines'][0]['to'];
                                $grouped['to_id'] = $group['lines'][0]['to_id'];
                            } else {
                                $grouped['to'] = sprintf_wp('Other (%s)', $direction);
                                $grouped['to_id'] =0;
                                $grouped['from'] = $group['lines'][0]['from'];
                                $grouped['from_id'] = $group['lines'][0]['from_id'];
                            }
                            $byType[$direction][] = $grouped;
                        }
                    }
                }
                // смержим сгруппированные (или нет) тонкие и толстые
                $byType[$direction] = array_merge($byType[$direction], $grouping[$direction]['lines']);
            }

            $chartData[$currency]['data'] = array_merge($byType['income'], $byType['expense']);

            // доберем income
            $incomeUnique = array_unique(array_column($byType['income'], 'to'));
            foreach ($byType['expense'] as $item) {
                if (!in_array($item['from'], $incomeUnique, true)) {
                    $chartData[$currency]['data'][] = [
                        'from' => 'stub',
                        'from_id' => 0,
                        'to' => $item['from'],
                        'to_id' => 0,
                        'value' => 0,
                        'direction' => 'income',
                        'color' => '',
                        'currency' => $datum['details']->getCode(),
                        'currencySign' => $datum['details']->getSign(),
                    ];
                }
            }

            $byType = array_reduce(
                $datumData,
                static function (array $carry, array $line) {
                    $carry[$line['direction']][] = $line[$line['direction'] === 'income' ? 'from' : 'to'];

                    return $carry;
                },
                ['income' => [], 'expense' => []]
            );

            foreach ($chartData[$currency]['data'] as $i => $renameDatum) {
                if ($renameDatum['direction'] === 'income') {
                    if (in_array($renameDatum['from'], $byType['expense'], true)) {
                        $chartData[$currency]['data'][$i]['from'] = sprintf('%s (%s)', $renameDatum['from'],
                            _w($renameDatum['direction']));
                    }
                } else {
                    if (in_array($renameDatum['to'], $byType['income'], true)) {
                        $chartData[$currency]['data'][$i]['to'] = sprintf('%s (%s)', $renameDatum['to'],
                            _w($renameDatum['direction']));
                    }
                }

            }
        }

        return $chartData;
    }
}
