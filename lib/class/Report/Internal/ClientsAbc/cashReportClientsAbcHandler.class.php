<?php

final class cashReportClientsAbcHandler implements cashReportHandlerInterface
{
    public function canHandle(string $identifier): bool
    {
        return $identifier === 'clients-abc';
    }

    public function handle(array $params): string
    {
        $reportService = new cashReportClientsAbcService();

        $from = DateTimeImmutable::createFromFormat('Y-m-d', $params['from'] ?? date('Y-m-d', strtotime('-365 days')));
        if ($from === false) {
            throw new cashValidateException('Wrong from');
        }

        $to = DateTimeImmutable::createFromFormat('Y-m-d', $params['to'] ?? date('Y-m-d'));
        if ($to === false) {
            throw new cashValidateException('Wrong to');
        }

        if (empty($params['currency'])) {
            /** @var cashAccount $account */
            $account = cash()->getEntityRepository(cashAccount::class)->findFirstForContact();
            $params['currency'] = $account->getCurrency();
        }

        $data = $reportService->getDataForPeriodAndCurrency($from, $to, $params['currency']);

        $total = array_sum($data);

        $a = $params['a'] ?? 80;
        $b = $params['b'] ?? 15;
        $c = $params['c'] ?? 5;

        $tableData = [
            'a' => new cashReportClientsAbcLetterDto('A', new cashReportClientsAbcValueDto(0, 0)),
            'b' => new cashReportClientsAbcLetterDto('B', new cashReportClientsAbcValueDto(0, 0)),
            'c' => new cashReportClientsAbcLetterDto('C', new cashReportClientsAbcValueDto(0, 0)),
        ];

        foreach ($data as $contractorId => $value) {
            $client = new waContact($contractorId);
            $percent = ($value * 100) / $total;
            $letter = $this->chooseAbc($b, $c, $percent);
            $tableData[$letter]->value->amount += $value;
            $tableData[$letter]->clients[] = new cashReportClientsAbcClientDto(
                $client->getId(), $client->getName(), $client->getPhoto(),
                new cashReportClientsAbcValueDto(
                    round($value, 2),
                    round($percent, 3)
                )
            );
        }

        foreach ($tableData as $tableDatum) {
            if (!$total) {
                continue;
            }

            $tableDatum->value->percent = round(($tableDatum->value->amount * 100) / $total, 3);
            usort(
                $tableDatum->clients,
                static function (cashReportClientsAbcClientDto $client1, cashReportClientsAbcClientDto $client2) {
                    return $client1->value->amount <= $client2->value->amount ? 1 : -1;
                }
            );
        }

        /** @var array<cashAccount> $accounts */
        $accounts = cash()->getEntityRepository(cashAccount::class)
            ->findAllActiveForContact();
        $currencies = [];
        foreach ($accounts as $account) {
            if (!isset($currencies[$account->getCurrency()])) {
                $currencies[$account->getCurrency()] = cashCurrencyVO::fromWaCurrency($account->getCurrency());
            }
        }

        return wa()->getView()->renderTemplate(
            wa()->getAppPath('templates/actions/report/internal/ReportClientsAbc.html'),
            [
                'tableData' => $tableData,
                'a' => $a,
                'b' => $b,
                'c' => $c,
                'total' => $total,
                'currencies' => $currencies,
                'currency' => cashCurrencyVO::fromWaCurrency($params['currency']),
                'from' => $from->format('Y-m-d'),
                'to' => $to->format('Y-m-d'),
            ],
            true
        );
    }

    private function chooseAbc($b, $c, $percent): string
    {
        if ($percent <= $c) {
            return 'c';
        }

        if ($percent <= $b) {
            return 'b';
        }

        return 'a';
    }
}
