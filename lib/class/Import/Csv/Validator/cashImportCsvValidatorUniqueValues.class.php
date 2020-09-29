<?php

/**
 * Class cashImportCsvValidatorUniqueValues
 */
class cashImportCsvValidatorUniqueValues extends cashImportCsvAbstractValidator implements cashImportCvsValidatorInterface
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

        try {
            $this->response = $this->csvImport->getColumnUniqueValues($columnName);

            if (!$this->csvImport->canBeColumnWithUniqueValues(count($this->response))) {
                $this->errors[] = _w('This column has too many unique values, so that import conditions setup seems not applicable here') . ' (' . count($this->response) . ')';

                return false;
            }

            if (empty($this->response)) {
                $this->errors[] = _w('This column has no unique values, so that import conditions setup seems not applicable here');

                return false;
            }

            return true;
        } catch (Exception $exception) {
            $this->errors[] = $exception->getMessage();
            cash()->getLogger()->error($exception->getMessage(), $exception);
        }

        return false;
    }
}
