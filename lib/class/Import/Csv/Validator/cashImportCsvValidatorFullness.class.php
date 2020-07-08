<?php

/**
 * Class cashImportCsvValidatorFullness
 */
class cashImportCsvValidatorFullness extends cashImportCsvAbstractValidator implements cashImportCvsValidatorInterface
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
            if (empty($columnName)) {
                $fullness = 100;
            } else {
                $fullness = $this->csvImport->getColumnFullness($columnName);
            }

            if ($fullness < 100) {
                $this->errors[] = sprintf_wp(
                    '<b>Only %d out of %d rows in the file have this column defined.</b> Remaining <span class="highlighted">%d rows have an undefined value</span> and thus will be skipped. Please make sure the right column is selected.',
                    $this->csvImport->getColumnValues($columnName),
                    $this->csvImport->getCsvInfoDto()->totalRows,
                    $this->csvImport->getCsvInfoDto()->totalRows - $this->csvImport->getColumnValues($columnName)
                );

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
