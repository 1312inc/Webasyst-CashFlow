<?php

final class cashAggregateFilter
{
    public const FILTER_ACCOUNT    = 'account';
    public const FILTER_CATEGORY   = 'category';
    public const FILTER_CONTRACTOR = 'contractor';
    public const FILTER_IMPORT     = 'import';
    public const FILTER_TRASH      = 'trash';
    public const FILTER_CURRENCY   = 'currency';
    public const FILTER_SEARCH     = 'search';
    public const FILTER_EXTERNAL   = 'external';

    /**
     * @var int|null
     */
    private $account;

    /**
     * @var int|null
     */
    private $category;

    /**
     * @var string|null
     */
    private $currency;

    /**
     * @var int|null
     */
    private $contractor;

    /**
     * @var int|null
     */
    private $import;

    /**
     * @var string|null
     */
    private $search;

    /**
     * @var int|null
     */
    private $trash;

    /**
     * @var int|null
     */
    private $external;

    /**
     * @var string|null
     */
    private $externalSource;

    public static function createFromHash(?string $hash): cashAggregateFilter
    {
        $self = new self;

        if ($hash) {
            [$filter, $identifier] = explode('/', $hash);

            if ($filter === self::FILTER_EXTERNAL) {
                $externalData = explode('.', $identifier);
                if (count($externalData) === 2) {
                    $self->externalSource = $externalData[0];
                    $self->external = (int) $externalData[1];
                }
            } elseif (property_exists($self, $filter)) {
                $self->$filter = in_array(
                    $filter,
                    [
                        self::FILTER_ACCOUNT,
                        self::FILTER_CATEGORY,
                        self::FILTER_CONTRACTOR,
                        self::FILTER_IMPORT,
                        self::FILTER_TRASH,
                    ],
                    true
                ) ? (int) $identifier : (string) $identifier;
            }
        }

        return $self;
    }

    public function getAccountId(): ?int
    {
        return $this->account;
    }

    public function getCategoryId(): ?int
    {
        return $this->category;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function getContractorId(): ?int
    {
        return $this->contractor;
    }

    public function getImportId(): ?int
    {
        return $this->import;
    }

    public function getSearch(): ?string
    {
        return $this->search;
    }

    public function getTrash(): ?int
    {
        return $this->trash;
    }

    public function isFilterByCurrency(): bool
    {
        return !empty($this->currency);
    }

    public function isFilterByAccount(): bool
    {
        return !empty($this->account);
    }

    public function getExternalId(): ?int
    {
        return $this->external;
    }

    public function getExternalSource(): ?string
    {
        return $this->externalSource;
    }
}
