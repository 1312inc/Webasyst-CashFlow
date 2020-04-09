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
                'message' => sprintf(
                    '<i class="icon10 exclamation"></i> Only %d out of %d transactions have this column defined. Please make sure the right column is selected.',
                    $csvImport->getColumnValues($columnName),
                    $csvImport->getCsvInfoDto()->totalRows
                ),
            ];
        } catch (Exception $exception) {
            $this->errors[] = $exception->getMessage();
            cash()->getLogger()->error($exception->getMessage(), $exception);
        }
    }
}
