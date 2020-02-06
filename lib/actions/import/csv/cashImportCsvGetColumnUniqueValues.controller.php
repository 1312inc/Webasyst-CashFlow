<?php

/**
 * Class cashImportCsvGetColumnUniqueValuesController
 */
class cashImportCsvGetColumnUniqueValuesController extends cashJsonController
{
    /**
     * @throws Exception
     */
    public function execute()
    {
        $columnName = waRequest::get('column_name', '', waRequest::TYPE_STRING_TRIM);

        try {
            $csvImport = cashImportCsv::createCurrent();
            $this->response = $csvImport->getColumnUniqueValues($columnName);

            if (!$csvImport->canBeColumnWithUniqueValues(count($this->response))) {
                $this->errors[] = _w('This column has too many unique values') . ' (' . count($this->response) . ')';
            }

            if (empty($this->response)) {
                $this->errors[] = _w('This column has no unique values');
            }
        } catch (Exception $exception) {
            $this->errors[] = $exception->getMessage();
            cash()->getLogger()->error($exception->getMessage(), $exception);
        }
    }
}
