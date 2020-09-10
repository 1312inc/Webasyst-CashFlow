<?php

/**
 * Class cashApiAccountResponse
 */
class cashApiAccountResponse extends cashAbstractDto
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
    public $description;

    /**
     * @var string
     */
    public $icon;

    /**
     * @var string
     */
    public $currency;

    /**
     * @var int
     */
    public $customer_contact_id;

    /**
     * @var bool
     */
    public $is_archived;

    /**
     * @var int
     */
    public $sort;

    /**
     * @var string
     */
    public $create_datetime;

    /**
     * @var string|null
     */
    public $update_datetime;

    /**
     * @var array
     */
    public $stat;

    /**
     * cashApiTransactionDto constructor.
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->initializeWithArray($data);
    }

    /**
     * @param cashAccount $account
     *
     * @return cashApiAccountResponse
     */
    public static function fromAccount(cashAccount $account): cashApiAccountResponse
    {
        $dto = new self();
        $dto->id = (int) $account->getId();
        $dto->name = $account->getName();
        $dto->description = $account->getDescription();
        $dto->icon = $account->getIcon();
        $dto->currency = $account->getCurrency();
        $dto->customer_contact_id = $account->getCustomerContactId() ? (int) $account->getCustomerContactId() : null;
        $dto->is_archived = $account->getIsArchived() ? true : false;
        $dto->create_datetime = $account->getCreateDatetime();
        $dto->update_datetime = $account->getUpdateDatetime();

        return $dto;
    }
}
