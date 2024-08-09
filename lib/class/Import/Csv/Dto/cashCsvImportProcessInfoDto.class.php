<?php

/**
 * Class cashCsvImportProcessInfoDto
 */
class cashCsvImportProcessInfoDto implements JsonSerializable
{
    public $totalRows = 0;

    public $passedRows = 0;

    public $time = 0;

    public $memory = 0;

    public $chunk = cashImportCsv::ROWS_TO_READ;

    public $path;

    public $processId;

    public $done = false;

    public $error = '';

    public $ok = 0;

    public $fail = 0;

    /**
     * @var cashAccountDto[]
     */
    public $accounts = [];

    /**
     * @var cashCategoryDto[]
     */
    public $categories = [];

    /**
     * @var int
     */
    public $importId;

    /**
     * cashCsvImportProcessInfoDto constructor.
     *
     * @param string $processId
     * @param int    $importId
     */
    public function __construct($processId, $importId)
    {
        $this->processId = $processId;
        $this->importId = $importId;
    }

    /**
     * @inheritDoc
     */
    #[ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return [
            'total_rows' => $this->totalRows,
            'passed_rows' => $this->passedRows,
            'time' => $this->time,
            'memory' => $this->memory,
            'processId' => $this->processId,
            'ready' => $this->done,
            'progress' => min(100, round($this->passedRows / $this->totalRows * 100)),
            'error' => $this->error,
            'transactions' => [
                'ok' => $this->ok,
                'fail' => $this->fail,
            ],
            'import_id' => $this->importId,
        ];
    }
}