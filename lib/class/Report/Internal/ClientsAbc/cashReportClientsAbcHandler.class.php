<?php

final class cashReportClientsAbcHandler implements cashReportHandlerInterface
{
    public function canHandle(string $identifier): bool
    {
        return $identifier === 'clients-abc';
    }

    public function handle(array $params): string
    {
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

        $current_company_id = $params['company'] ?? 0;
        $companies = [['id' => 0, 'name' => _w('Все компании')]] + cash()->getModel('cashCompany')->getAll('id');

        $reportService = new cashReportClientsAbcService();
        $data = $reportService->getDataForPeriodAndCurrency($current_company_id, $from, $to, $params['currency']);

        $a = $params['a'] ?? 80;
        $b = $params['b'] ?? 15;
        $c = $params['c'] ?? 5;

        $tableData = [
            'a' => new cashReportClientsAbcLetterDto($a, 'A', new cashReportClientsAbcValueDto(0, 0)),
            'b' => new cashReportClientsAbcLetterDto($b, 'B', new cashReportClientsAbcValueDto(0, 0)),
            'c' => new cashReportClientsAbcLetterDto($c, 'C', new cashReportClientsAbcValueDto(0, 0)),
        ];

        $clients = [];
        $total = array_sum($data);
        foreach ($data as $contractorId => $value) {
            $client = new waContact($contractorId);
            if (!$client->exists()) {
                continue;
            }

            $percent = ($value * 100) / $total;
            $clients[] = new cashReportClientsAbcClientDto(
                $client->getId(),
                $client->getName(),
                $client->getPhoto(),
                new cashReportClientsAbcValueDto(
                    round($value, 2),
                    round($percent, 2)
                )
            );
        }

        usort(
            $clients,
            static function (cashReportClientsAbcClientDto $client1, cashReportClientsAbcClientDto $client2) {
                return $client1->value->amount <= $client2->value->amount ? 1 : -1;
            }
        );

        /** @var cashReportClientsAbcLetterDto $tableDatum */
        foreach ($tableData as $tableDatum) {
            /** @var cashReportClientsAbcClientDto $client */
            while (count($clients)) {
                $client = reset($clients);
                $tableDatum->value->percent += $client->value->percent;
                $tableDatum->value->amount += $client->value->amount;

                $tableDatum->clients[] = array_shift($clients);

                if ($tableDatum->value->percent >= $tableDatum->targetPercent) {
                    break;
                }
            }
        }

        /** @var array<cashAccount> $accounts */
        $accounts = cash()->getEntityRepository(cashAccount::class)->findAllActiveForContact();
        $currencies = [];
        foreach ($accounts as $account) {
            if (!isset($currencies[$account->getCurrency()])) {
                $currencies[$account->getCurrency()] = cashCurrencyVO::fromWaCurrency($account->getCurrency());
            }
        }

        return wa()->getView()->renderTemplate(
            wa()->getAppPath('templates/actions/report/internal/ReportClientsAbc.html'),
            [
                'companies' => $companies,
                'current_company_id' => $current_company_id,
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
}
