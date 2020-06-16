<?php

/**
 * Class cashCsvImportInfoDto
 */
class cashCsvImportInfoDto
{
    /**
     * @var array
     */
    public $headers = [];

    /**
     * @var array
     */
    public $uniqueValues = [];

    /**
     * @var string
     */
    public $path = '';

    /**
     * @var string
     */
    public $delimiter = '';

    /**
     * @var string
     */
    public $encoding = '';

    /**
     * @var int
     */
    public $totalRows = 0;

    /**
     * @var array
     */
    public $totalRowsByColumn = [];

    /**
     * @var array
     */
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
