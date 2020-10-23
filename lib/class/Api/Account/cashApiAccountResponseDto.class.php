<?php

/**
 * Class cashApiAccountResponseDto
 */
class cashApiAccountResponseDto
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
     * @param cashAccount $account
     *
     * @return cashApiAccountResponseDto
     */
    public static function fromAccount(cashAccount $account): cashApiAccountResponseDto
    {
        $dto = new self();
        $dto->id = (int) $account->getId();
        $dto->name = $account->getName();
        $dto->description = $account->getDescription();
        $dto->icon = $account->getIcon();
        if (strpos($dto->icon, cashLogoUploader::USER_ACCOUNT_LOGOS_PATH.'/') === 0) {
            $dto->icon = cashLogoUploader::getUrlToAccountLogo($dto->icon);
        }
        $dto->currency = $account->getCurrency();
        $dto->customer_contact_id = $account->getCustomerContactId() ? (int) $account->getCustomerContactId() : null;
        $dto->is_archived = $account->getIsArchived() ? true : false;
        $dto->sort = (int) $account->getSort();
        $dto->create_datetime = $account->getCreateDatetime();
        $dto->update_datetime = $account->getUpdateDatetime();

        return $dto;
    }
}
