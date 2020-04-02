<?php

/**
 * Class cashImportCsv
 */
final class cashImportCsv
{
    const PROVIDER_CSV = 'csv';
    const DEFAULT_ENCODING       = 'utf-8';
    const DEFAULT_DELIMITER      = ';';
    const MAX_UNIQUENESS_DIVIDER = 4;
    const MAX_UNIQUENESS_LIMIT   = 200;
    const FIRST_ROWS             = 100;
    const MAX_ROWS_TO_READ       = 100500;
    const ROWS_TO_READ           = 10;

    /**
     * @var cashImportResponseCsv
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
     * @var cashCsvImportSettings
     */
    private $settings;

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
     * @param int $firstRows
     *
     * @return cashImportResponseCsv
     */
    public function collectInfo($firstRows = self::FIRST_ROWS)
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
                if ($row > 1 && $row < $firstRows) {
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

        $response->setCsvInfoDto($this->csvInfoDto);

        return $response;
    }

    /**
     * @param array $headers
     * @param int   $startRow
     * @param int   $rowsToRead
     *
     * @return cashCsvDataDto
     */
    public function process(array $headers, $startRow = 0, $rowsToRead = self::ROWS_TO_READ)
    {
        $response = new cashCsvDataDto();
        $handle = fopen($this->path, 'rb');
        if ($handle === false) {
            return $response;
        }

        try {
            while (($data = fgetcsv($handle, 0, $this->delimiter)) !== false) {
                $response->rows++;

                if ($startRow === 0 && $response->rows === 1) {
                    continue;
                }
                if ($response->rows > $startRow + $rowsToRead) {
                    break;
                }
                if ($response->rows < $startRow) {
                    continue;
                }
                if (!$this->notEmptyArray($data)) {
                    continue;
                }

                $response->data[$response->rows] = array_combine($headers, $this->encodeArray($data));
            }
        } catch (Exception $ex) {
            $this->error = _w('Error on csv processing');
            cash()->getLogger()->error(
                sprintf('Error on csv processing: %d - %d', $startRow, ($startRow + $rowsToRead)),
                $ex
            );
        } finally {
            fclose($handle);
        }

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
        return (int)$count <= $this->getCsvInfoDto()->totalRows / self::MAX_UNIQUENESS_DIVIDER
            || ((int)$count > $this->getCsvInfoDto()->totalRows / self::MAX_UNIQUENESS_DIVIDER && $this->getCsvInfoDto()->totalRows < self::MAX_UNIQUENESS_LIMIT);
    }

    /**
     * @return cashCsvImportInfoDto
     */
    public function getCsvInfoDto()
    {
        return $this->csvInfoDto;
    }

    /**
     * @param array                       $data
     * @param cashCsvImportProcessInfoDto $infoDto
     *
     * @return bool
     * @throws waException
     */
    public function save(array $data, cashCsvImportProcessInfoDto $infoDto)
    {
        /** @var cashTransactionModel $model */
        $model = cash()->getModel(cashTransaction::class);

        /** @var cashTransaction $transaction */
        $transaction = cash()->getEntityFactory(cashTransaction::class)->createNew();

        try {
            cash()->getLogger()->debug($data, 'import');

            $transaction
                ->setAmount($this->getAmount($data))
                ->setDescription($this->getDescription($data))
                ->setDate($this->getDate($data))
                ->setAccountId($this->getAccount($data, $infoDto))
                ->setCategoryId(
                    $this->getCategory(
                        $data,
                        $infoDto,
                        $transaction->getAmount() < 0 ? cashCategory::TYPE_EXPENSE : cashCategory::TYPE_INCOME
                    )
                )
                ->setImportId($infoDto->importId);

            $selectedCategory = $transaction->getCategoryId();
            if ($selectedCategory) {
                $amount = abs($transaction->getAmount());
                if ($infoDto->categories[$selectedCategory]->type === cashCategory::TYPE_EXPENSE) {
                    $transaction->setAmount(-$amount);
                } else {
                    $transaction->setAmount($amount);
                }
            }

            cash()->getLogger()->debug($transaction, 'import');
        } catch (Exception $ex) {
            $infoDto->fail++;
            $this->error = sprintf('Error on transaction import create transaction: %s', $ex->getMessage());

            return false;
        }

        $alreadyExists = false;

        if ($this->settings->isSkipDuplicates()) {
            $alreadyExists = $model->countByField(
                [
                    'account_id' => $transaction->getAccountId(),
                    'category_id' => $transaction->getCategoryId(),
                    'date' => $transaction->getDate(),
                    'amount' => $transaction->getAmount(),
                ]
            );
        }

        if (!$alreadyExists) {
            $model->startTransaction();
            try {
                cash()->getEntityPersister()->save($transaction);
                $model->commit();
                $infoDto->ok++;

                return true;
            } catch (Exception $ex) {
                $model->rollback();
                $infoDto->fail++;
                $this->error = sprintf('Error on transaction import save: %s', $ex->getMessage());
            }
        } else {
            $infoDto->fail++;
            $this->error = 'Transaction already exists';
        }

        return false;
    }

    /**
     * @param cashCsvImportSettings $settings
     *
     * @return cashImportCsv
     */
    public function setSettings(cashCsvImportSettings $settings)
    {
        $this->settings = $settings;

        return $this;
    }

    /**
     * @param array $a
     *
     * @return mixed
     */
    private function encodeArray($a)
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
    private function notEmptyArray(array $a)
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

    /**
     * @param array $data
     *
     * @return float
     * @throws kmwaLogicException
     */
    private function getAmount(array $data)
    {
        $amount = 0;
        switch ($this->settings->getAmountType()) {
            case cashCsvImportSettings::TYPE_SINGLE:
                $amount = $this->toFloat($data[$this->settings->getAmount()]);
                break;

            case cashCsvImportSettings::TYPE_MULTI:
                if (!empty($data[$this->settings->getIncome()])) {
                    $amount = abs($this->toFloat($data[$this->settings->getIncome()]));
                } elseif (!empty($data[$this->settings->getExpense()])) {
                    $amount = -abs($this->toFloat($data[$this->settings->getExpense()]));
                }
                break;
        }

        if (empty($amount)) {
            throw new kmwaLogicException('No amount in imported data');
        }

        return $amount;
    }

    /**
     * @param array $data
     *
     * @return string|null
     */
    private function getDescription(array $data)
    {
        $description = null;
        if (!empty($data[$this->settings->getDescription()])) {
            $description = trim($data[$this->settings->getDescription()]);
        }

        return $description;
    }

    /**
     * @param array $data
     *
     * @return string
     * @throws kmwaLogicException
     */
    private function getDate(array $data)
    {
        try {
            $date = cashDatetimeHelper::createDateTimeFromFormat(
                $data[$this->settings->getDatetime()],
                $this->settings->getDateformat()
            );

            if ($date instanceof DateTime) {
                return $date->format('Y-m-d H:i:s');
            }
        } catch (Exception $exception) {
            cash()->getLogger()->error($exception->getMessage(), $exception);
        }

        throw new kmwaLogicException('No date in imported data');
    }

    /**
     * @param array                       $data
     * @param cashCsvImportProcessInfoDto $infoDto
     *
     * @return int
     * @throws kmwaLogicException
     */
    private function getAccount(array $data, cashCsvImportProcessInfoDto $infoDto)
    {
        $accountId = 0;
        $accountHeader = $this->settings->getAccount();

        switch ($this->settings->getAccountType()) {
            case cashCsvImportSettings::TYPE_SINGLE:
                if (isset($infoDto->accounts[$accountHeader])) {
                    $accountId = $infoDto->accounts[$accountHeader]->id;
                }
                break;

            case cashCsvImportSettings::TYPE_MULTI:
                $accountMap = $this->settings->getAccountMap();
                if (isset($data[$accountHeader], $accountMap[$data[$accountHeader]]) && $accountMap[$data[$accountHeader]] > 0) {
                    $accountId = $accountMap[$data[$accountHeader]];
                }
//                if (isset($accountMap[$accountHeader]) && isset($infoDto->accounts[$accountMap[$accountHeader]])) {
//                    $accountId = $infoDto->accounts[$accountMap[$accountHeader]]->id;
//                }
//                break;
        }

        if (empty($accountId)) {
            throw new kmwaLogicException('No account in imported data');
        }

        return $accountId;
    }

    /**
     * @param array                       $data
     * @param cashCsvImportProcessInfoDto $infoDto
     * @param string                      $type
     *
     * @return int|null
     */
    private function getCategory(array $data, cashCsvImportProcessInfoDto $infoDto, $type = cashCategory::TYPE_INCOME)
    {
        $categoryId = null;

        switch ($this->settings->getCategoryType()) {
            case cashCsvImportSettings::TYPE_SINGLE:
                if ($type === cashCategory::TYPE_INCOME) {
                    $_catId = $this->settings->getCategoryIncome();
                } else {
                    $_catId = $this->settings->getCategoryExpense();
                }
                $categoryId = isset($infoDto->categories[$_catId]) ? $infoDto->categories[$_catId]->id : null;
                break;

            case cashCsvImportSettings::TYPE_MULTI:
                $categoryMap = $this->settings->getCategoryMap();
                $category = $this->settings->getCategory();
                if (isset($data[$category], $categoryMap[$data[$category]]) && $categoryMap[$data[$category]] > 0) {
                    $categoryId = $categoryMap[$data[$category]];
                }

                break;
        }

        return $categoryId;
    }

    /**
     * @param $value
     *
     * @return float
     */
    private function toFloat($value)
    {
        return (float)str_replace(',','.',$value);
    }
}
