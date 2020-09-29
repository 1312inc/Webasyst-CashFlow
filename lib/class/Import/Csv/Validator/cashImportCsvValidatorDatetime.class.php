<?php

/**
 * Class cashImportCsvValidatorDatetime
 */
class cashImportCsvValidatorDatetime extends cashImportCsvAbstractValidator implements cashImportCvsValidatorInterface
{
    /**
     * @param array $params
     *
     * @return bool
     */
    public function validate(...$params): bool
    {
        $columnName = waRequest::get('column_name', '', waRequest::TYPE_STRING_TRIM);
        $dateformat = waRequest::get('dateformat', '', waRequest::TYPE_STRING_TRIM);

        $values = $this->csvImport->getColumnUniqueValues($columnName);

        try {
            $converted = [];
            $errorsCount = 0;
            foreach ($values as $date) {
                if (empty($date)) {
                    continue;
                }

                try {
                    $converted[$date] = cashDatetimeHelper::createDateTimeFromFormat($date, $dateformat);
                    if (!preg_match('/.*[\.\-\/\\:].*/', $date)) {
                        $this->errors[] = _w('Some dates do not have proper delimiter');

                        return false;
                    }
                } catch (Exception $exception) {
                    $errorsCount++;

                    if ($errorsCount > 5) {
                        $this->errors[] = _w('Some dates might be in the wrong format');

                        return false;
                    }
                }
            }

            $converted = array_filter($converted);
            if (empty($converted)) {
                $this->errors[] = sprintf_wp('The column %s contains no dates in the selected format', $columnName);

                return false;
            }

            $first = reset($converted);
            $this->response = [
                'source' => key($converted),
                'converted' => waDateTime::format(
                    'humandate',
                    $first->format('Y-m-d'),
                    date_default_timezone_get()
                ),
            ];

            return true;
        } catch (Exception $exception) {
            $this->errors[] = $exception->getMessage();
            cash()->getLogger()->error($exception->getMessage(), $exception);
        }

        return false;
    }

}