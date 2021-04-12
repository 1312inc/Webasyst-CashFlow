<?php

/**
 * Class cashImportAction
 */
class cashExportCsvAction extends cashViewAction
{
    /**
     * @param null|array $params
     *
     * @return mixed
     * @throws kmwaLogicException
     * @throws kmwaNotFoundException
     * @throws kmwaRuntimeException
     * @throws waException
     */
    public function runAction($params = null)
    {
        $settings = waRequest::request('settings', waRequest::TYPE_ARRAY_TRIM, []);

        if (empty($settings)) {
            throw new kmwaRuntimeException(_w('No settings to export CSV'));
        }

        switch (waRequest::request('type', waRequest::TYPE_STRING_TRIM, '')) {
            case 'upcoming':
                $startDate = (new DateTime('tomorrow'))->modify('midnight');
                $endDate = DateTime::createFromFormat('Y-m-d H:i:s', $settings['end_date']);
                break;

            case 'completed':
                $startDate = DateTime::createFromFormat('Y-m-d H:i:s', $settings['start_date']);
                $endDate = (new DateTime())->modify('midnight');
                break;

            default:
                $startDate = DateTime::createFromFormat('Y-m-d H:i:s', $settings['start_date']);
                $endDate = DateTime::createFromFormat('Y-m-d H:i:s', $settings['end_date']);
        }

        if (!$endDate && !$startDate) {
            throw new waException('Wrong dates');
        }

        $filterDto = new cashTransactionPageFilterDto($settings['entity_type'], $settings['entity_id']);

        /** @var cashTransactionRepository $repository */
        $repository = cash()->getEntityRepository(cashTransaction::class);
        $data = array_reverse($repository->findByDatesAndFilter($startDate, $endDate, $filterDto));
        $filename = sprintf(
            'cash_%s_%s_%s_utf8.csv',
            mb_strtoupper($filterDto->name),
            $startDate->format('Ymd'),
            $endDate->format('Ymd')
        );

        $service = new cashExportCsv();
        $importedFilepath = $service->process($filename, array_reverse($data, true));
        waFiles::readFile($importedFilepath, $filename);
    }
}
