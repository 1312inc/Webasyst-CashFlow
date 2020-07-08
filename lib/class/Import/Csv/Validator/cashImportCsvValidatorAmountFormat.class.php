<?php

/**
 * Class cashImportCsvValidatorAmountFormat
 */
class cashImportCsvValidatorAmountFormat extends cashImportCsvAbstractValidator implements cashImportCvsValidatorInterface
{
    /**
     * @param array $params
     *
     * @return bool
     * @throws kmwaLogicException
     */
    public function validate(...$params): bool
    {
        $columnName = waRequest::get('column_name', '', waRequest::TYPE_STRING_TRIM);

        $values = $this->csvImport->getColumnUniqueValues($columnName);

        try {
            foreach ($values as $amount) {
                if (empty($amount)) {
                    continue;
                }

                if (!preg_match('/[\d,.\- ]/', $amount)) {
                    $this->errors[] = _w('Some amount do not have proper format');

                    return false;
                }
            }

            return true;
        } catch (Exception $exception) {
            $this->errors[] = $exception->getMessage();
            cash()->getLogger()->error($exception->getMessage(), $exception);
        }

        return false;
    }
}
