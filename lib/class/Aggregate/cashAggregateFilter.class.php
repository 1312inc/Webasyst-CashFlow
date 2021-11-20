<?php

final class cashAggregateFilter
{
    public const FILTER_ACCOUNT    = 'account';
    public const FILTER_CATEGORY   = 'category';
    public const FILTER_CONTRACTOR = 'contractor';
    public const FILTER_IMPORT     = 'import';
    public const FILTER_CURRENCY   = 'currency';
    public const FILTER_SEARCH     = 'search';

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

    public static function createFromHash(?string $hash): cashAggregateFilter
    {
        $self = new self;

        if ($hash) {
            [$filter, $identifier] = explode('/', $hash);

            if (property_exists($self, $filter)) {
                $self->$filter = in_array(
                    $filter,
                    [self::FILTER_ACCOUNT, self::FILTER_CATEGORY, self::FILTER_CONTRACTOR, self::FILTER_IMPORT],
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

    public function isFilterByCurrency(): bool
    {
        return !empty($this->currency);
    }

    public function isFilterByAccount(): bool
    {
        return !empty($this->account);
    }
}
