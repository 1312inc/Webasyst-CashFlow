<?php


abstract class cashImportCsvAbstractValidator
{
    const DATETIME = 'datetime';
    const AMOUNT = 'amount';
    const UNIQUE_VALUES = 'unique_values';
    const FULLNESS = 'fullness';

    /**
     * @var array
     */
    protected $errors = [];

    /**
     * @var array
     */
    protected $response = [];

    /**
     * @var cashImportCsv
     */
    protected $csvImport;

    /**
     * cashImportCsvAbsctractValidator constructor.
     *
     * @param cashImportCsv $csvImport
     */
    public function __construct(cashImportCsv $csvImport)
    {
        $this->csvImport = $csvImport;
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @return array
     */
    public function getResponse(): array
    {
        return $this->response;
    }
}
