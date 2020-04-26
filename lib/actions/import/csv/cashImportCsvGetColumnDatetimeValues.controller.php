<?php

/**
 * Class cashImportCsvGetColumnDatetimeValuesController
 */
class cashImportCsvGetColumnDatetimeValuesController extends cashJsonController
{
    /**
     * @throws Exception
     */
    public function execute()
    {
        $columnName = waRequest::get('column_name', '', waRequest::TYPE_STRING_TRIM);
        $dateformat = waRequest::get('dateformat', '', waRequest::TYPE_STRING_TRIM);

        try {
            $csvImport = cashImportCsv::createCurrent();
            $dates = $csvImport->getColumnUniqueValues($columnName);

            $converted = [];
            $errorsCount = 0;
            foreach ($dates as $date) {
                if (empty($date)) {
                    continue;
                }

                try {
                    $converted[$date] = cashDatetimeHelper::createDateTimeFromFormat($date, $dateformat);
                    if (!preg_match('/.*[\.\-\/\\:].*/', $date)) {
                        $this->errors[] = _w('Some dates do not have proper delimiter');

                        return;
                    }
                } catch (Exception $exception) {
                    $errorsCount++;

                    if ($errorsCount > 5) {
                        $this->errors[] = _w('Some dates might be in the wrong format');

                        return;
                    }
                }
            }

            $converted = array_filter($converted);
            if (empty($converted)) {
                $this->errors[] = sprintf_wp('The column %s contains no dates in the selected format', $columnName);
            } else {
                $first = reset($converted);
                $this->response = [
                    'source' => key($converted),
                    'converted' => waDateTime::format(
                        'humandate',
                        $first->format('Y-m-d'),
                        date_default_timezone_get()
                    ),
                ];
            }
        } catch (Exception $exception) {
            $this->errors[] = $exception->getMessage();
            cash()->getLogger()->error($exception->getMessage(), $exception);
        }
    }
}
