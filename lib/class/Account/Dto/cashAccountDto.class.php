<?php

/**
 * Class cashAccountDto
 */
class cashAccountDto extends cashAbstractDto
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $description = '';

    /**
     * @var string
     */
    public $icon = '';

    /**
     * @var string
     */
    public $iconLink = '';

    /**
     * @var string
     */
    public $currency;

    /**
     * @var float
     */
    public $currentBalance = 0.0;

    /**
     * @var int
     */
    public $customerContactId;

    /**
     * @var bool
     */
    public $isArchived;

    /**
     * @var int
     */
    public $sort;

    /**
     * @var string
     */
    public $createDatetime;

    /**
     * @var string
     */
    public $updateDatetime = '';

    /**
     * @var cashStatOnDateDto|null
     */
    public $stat;

    /**
     * cashAccountDto constructor.
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        if ($data) {
            $this->initializeWithArray($data);
            $this->currency = cashCurrencyVO::fromWaCurrency($data['currency']);
            if (isset($data['icon']) && strpos($data['icon'], 'http') !== false) {
                $this->iconLink = $this->icon;
                $this->icon = '';
            }
        } else {
            $this->name = _w('New account');
        }
    }

    /**
     * @param cashAccount $account
     *
     * @return static
     */
    public static function fromEntity(cashAccount $account)
    {
        /** @var static $dto */
        $dto = cashDtoFromEntityFactory::fromEntity(__CLASS__, $account);
        $dto->currency = cashCurrencyVO::fromWaCurrency($account->getCurrency());

        return $dto;
    }
}
