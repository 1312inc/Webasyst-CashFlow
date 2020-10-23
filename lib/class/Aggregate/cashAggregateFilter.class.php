<?php

/**
 * Class cashAggregateFilter
 */
class cashAggregateFilter
{
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
    private $contractorContact;

    /**
     * @param string|null $hash
     *
     * @return cashAggregateFilter
     */
    public static function createFromHash(?string $hash): cashAggregateFilter
    {
        $self = new self;

        if ($hash) {
            [$filter, $identifier] = explode('/', $hash);

            if (property_exists($self, $filter)) {
                $self->$filter = in_array($filter, ['account', 'category', 'contractorContact'])
                    ? (int) $identifier
                    : (string) $identifier;
            }
        }

        return $self;
    }

    /**
     * @return int|null
     */
    public function getAccountId(): ?int
    {
        return $this->account;
    }

    /**
     * @return int|null
     */
    public function getCategoryId(): ?int
    {
        return $this->category;
    }

    /**
     * @return string|null
     */
    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    /**
     * @return int|null
     */
    public function getContractorContactId(): ?int
    {
        return $this->contractorContact;
    }
}
