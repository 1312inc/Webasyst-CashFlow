<?php

/**
 * Class cashCsvImportInfoDto
 */
class cashCsvImportInfoDto
{
    public $headers = [];

    public $uniqueValues = [];

    public $path = '';

    public $delimiter = '';

    public $encoding = '';

    public $totalRows = 0;

    public $totalRowsByColumn = [];

    public $firstRows = [];

    /**
     * cashCsvImportInfoDto constructor.
     *
     * @param string $path
     * @param string $delimiter
     * @param string $encoding
     */
    public function __construct($path, $delimiter, $encoding)
    {
        $this->path = $path;
        $this->delimiter = $delimiter;
        $this->encoding = $encoding;
    }
}
