<?php

/**
 * Class cashImportCsv
 */
class cashImportCsv
{
    const DEFAULT_ENCODING  = 'utf-8';
    const DEFAULT_DELIMITER = ';';
    const MAX_UNIQUENESS_DIVIDER = 3;
    const MAX_UNIQUENESS_LIMIT = 200;
    const FIRST_ROWS = 100;

    /**
     * @var cashImportFileUploadedEventResponseInterface
     */
    protected $response;

    /**
     * @var string
     */
    protected $delimiter = self::DEFAULT_DELIMITER;

    /**
     * @var string
     */
    protected $encoding = self::DEFAULT_ENCODING;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $error;

    /**
     * @var cashCsvImportInfoDto
     */
    private $csvInfoDto;

    /**
     * cashImportCsv constructor.
     *
     * @param string $path
     * @param array  $settings
     */
    protected function __construct($path, array $settings = [])
    {
        $this->encoding = ifset($settings, 'encoding', self::DEFAULT_ENCODING);
        $this->delimiter = ifset($settings, 'delimiter', self::DEFAULT_DELIMITER);
        $this->path = $path;

        $cashInfoDto = cash()->getCache()->get(self::getCacheKeyForUser());
        if (!$cashInfoDto instanceof cashCsvImportInfoDto) {
            $this->csvInfoDto = new cashCsvImportInfoDto($this->path, $this->delimiter, $this->encoding);
        } else {
            $this->csvInfoDto = $cashInfoDto;
        }
    }

    /**
     * @param       $path
     * @param array $settings
     *
     * @return $this
     */
    public static function createNew($path, array $settings = [])
    {
        cash()->getCache()->delete(self::getCacheKeyForUser());

        return new static($path, $settings);
    }

    /**
     * @return static
     * @throws kmwaLogicException
     */
    public static function createCurrent()
    {
        $cashInfoDto = cash()->getCache()->get(self::getCacheKeyForUser());
        if (!$cashInfoDto instanceof cashCsvImportInfoDto) {
            throw new kmwaLogicException('No current import in progress');
        }

        return new static(
            $cashInfoDto->path,
            ['encoding' => $cashInfoDto->encoding, 'delimiter' => $cashInfoDto->delimiter]
        );
    }

    /**
     * @return cashImportResponseCsv
     */
    public function process()
    {
        $response = new cashImportResponseCsv();
        $row = 0;
        $handle = fopen($this->path, 'rb');
        if ($handle === false) {
            return $response;
        }

        $csvData = [];
        try {
            while (($data = fgetcsv($handle, 0, $this->delimiter)) !== false) {
                $row++;
                if (!$this->notEmptyArray($data)) {
                    continue;
                }

                $data = $this->encodeArray($data);
                for ($column = 0, $columnsCount = count($data); $column < $columnsCount; $column++) {
                    if ($row === 1) {
                        $this->csvInfoDto->headers[] = $data[$column];
                    } else {
                        $key = $this->csvInfoDto->headers[$column];
                        if (!isset($csvData[$key])) {
                            $csvData[$key] = [];
                        }
                        $csvData[$key][$row] = $data[$column];

                        if (!isset($this->csvInfoDto->totalRowsByColumn[$key])) {
                            $this->csvInfoDto->totalRowsByColumn[$key] = [];
                        }
                        if (!isset($this->csvInfoDto->totalRowsByColumn[$key][$data[$column]])) {
                            $this->csvInfoDto->totalRowsByColumn[$key][$data[$column]] = 0;
                        }
                        if (!empty($data[$column])) {
                            $this->csvInfoDto->totalRowsByColumn[$key][$data[$column]]++;
                        }
                    }
                }
                if ($row > 1 && $row < self::FIRST_ROWS) {
                    $this->csvInfoDto->firstRows[] = $data;
                }
            }
            $this->csvInfoDto->totalRows = $row - 1;
        } catch (Exception $ex) {
            $this->error = _w('Error on csv processing');
            cash()->getLogger()->error('Error on csv processing', $ex);
        } finally {
            fclose($handle);
        }

        // cache data
        foreach ($csvData as $key => $datum) {
            $this->csvInfoDto->uniqueValues[$key] = array_filter(array_unique($datum));
        }
        cash()->getCache()->set(self::getCacheKeyForUser(), $this->csvInfoDto);

        $response->setImportInfo($this->csvInfoDto);

        return $response;
    }

    /**
     * @return array
     */
    public static function getEncodings()
    {
        $encoding = array_diff(
            mb_list_encodings(),
            [
                'pass',
                'wchar',
                'byte2be',
                'byte2le',
                'byte4be',
                'byte4le',
                'BASE64',
                'UUENCODE',
                'HTML-ENTITIES',
                'Quoted-Printable',
                '7bit',
                '8bit',
                'auto',
            ]
        );

        $popular = array_intersect(['UTF-8', 'Windows-1251', 'ISO-8859-1',], $encoding);

        asort($encoding);
        $encoding = array_unique(array_merge($popular, $encoding));

        return $encoding;
    }

    /**
     * @return array
     */
    public static function getDelimiters()
    {
        return [
            _w('Semicolon') => self::DEFAULT_DELIMITER,
            _w('Comma') => ',',
            _w('Tab') => 'Tab',
        ];
    }

    /**
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param string $postfix
     *
     * @return string
     */
    public static function getCacheKeyForUser($postfix = '')
    {
        return sprintf('cashImport|csv|%s|%s', wa()->getUser()->getId(), $postfix);
    }

    /**
     * @param string $columnName
     *
     * @return array
     */
    public function getColumnUniqueValues($columnName)
    {
        return isset($this->csvInfoDto->uniqueValues[$columnName]) ? $this->csvInfoDto->uniqueValues[$columnName] : [];
    }

    /**
     * @param int $count
     *
     * @return bool
     */
    public function canBeColumnWithUniqueValues($count)
    {
        return (int)$count < $this->getCsvInfoDto()->totalRows / self::MAX_UNIQUENESS_LIMIT
            || $this->getCsvInfoDto()->totalRows < self::MAX_UNIQUENESS_LIMIT;
    }


    /**
     * @return cashCsvImportInfoDto
     */
    public function getCsvInfoDto()
    {
        return $this->csvInfoDto;
    }

    /**
     * @param array $a
     *
     * @return mixed
     */
    protected function encodeArray($a)
    {
        if ($this->encoding && is_array($a)) {
            foreach ($a as &$v) {
                @$v = iconv($this->encoding, "utf-8//IGNORE", $v);
            }
        }

        return $a;
    }

    /**
     * @param array $a
     *
     * @return bool
     */
    protected function notEmptyArray(array $a)
    {
        if (!$a) {
            return false;
        }
        if (!is_array($a)) {
            return false;
        }
        $t = false;
        foreach ($a as $v) {
            if (trim($v)) {
                return true;
            }
        }

        return $t;
    }
}
