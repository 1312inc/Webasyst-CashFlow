<?php

/**
 * Class cashImportCsvCheckColumnFullnessController
 */
class cashImportCsvCheckColumnFullnessController extends cashJsonController
{
    /**
     * @throws Exception
     */
    public function execute()
    {
        $columnName = waRequest::get('column_name', '', waRequest::TYPE_STRING_TRIM);

        try {
            $csvImport = cashImportCsv::createCurrent();

            if (empty($columnName)) {
                $fullness = 100;
            } else {
                $fullness = $csvImport->getColumnFullness($columnName);
            }

            $this->response = [
                'fullness' => $fullness,
                'message' => sprintf_wp(
                    '<b>Only %d out of %d rows in the file have this column defined.</b> Remaining <span class="highlighted">%d rows have an undefined value</span> and thus will be skipped. Please make sure the right column is selected.',
                    $csvImport->getColumnValues($columnName),
                    $csvImport->getCsvInfoDto()->totalRows,
                    $csvImport->getCsvInfoDto()->totalRows - $csvImport->getColumnValues($columnName)
                ),
            ];
        } catch (Exception $exception) {
            $this->errors[] = $exception->getMessage();
            cash()->getLogger()->error($exception->getMessage(), $exception);
        }
    }
}
