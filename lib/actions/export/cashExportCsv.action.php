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
                $endDate = new DateTime($settings['end_date']);
                break;

            case 'completed':
            default:
                $startDate = new DateTime($settings['start_date']);
                $endDate = new DateTime('midnight');
        }
        $filterDto = new cashTransactionPageFilterDto($settings['entity_type'], $settings['entity_id']);

        /** @var cashTransactionRepository $repository */
        $repository = cash()->getEntityRepository(cashTransaction::class);
        $data = $repository->findByDates($startDate, $endDate, $filterDto);
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
