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
            $this->response = [
                'fullness' => $csvImport->getColumnFullness($columnName),
                'message' => '<i class="icon10 exclamation"></i> '.sprintf_wp(
                    '<b>Only %d out of %d rows in the file have this column defined.</b> Remaining %d rows have the undefined value and thus will be skipped. Please make sure the right column is selected.',
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
