<?php

/**
 * Class cashAccount
 */
class cashAccount extends cashAbstractEntity
{
    use kmwaEntityDatetimeTrait;

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string|null
     */
    private $description;

    /**
     * @var string|null
     */
    private $icon;

    /**
     * @var string
     */
    private $currency;

    /**
     * @var int
     */
    private $customer_contact_id;

    /**
     * @var int
     */
    private $is_archived = 0;

    /**
     * @var int
     */
    private $sort = 0;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return cashAccount
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return cashAccount
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     *
     * @return cashAccount
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @param string|null $icon
     *
     * @return cashAccount
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * @return string
     */
    public function getIconHtml(): string
    {
        if (strpos($this->icon, 'http') !== false) {
            $html = sprintf(
                wa()->whichUI() == '1.3' ?
                    '<i class="icon16" style="background-image: url(\'%s\'); background-size: 16px 16px;"></i>'
                :
                    '<i class="icon" style="background-image: url(\'%s\'); background-size: 16px 16px;"></i>',
                $this->icon
            );
        } else {
            $html = sprintf( wa()->whichUI() == '1.3' ? '<i class="icon16 %s"></i>' : '', $this->icon);
        }

        return $html;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     *
     * @return cashAccount
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @return int
     */
    public function getCustomerContactId()
    {
        return $this->customer_contact_id;
    }

    /**
     * @param int $customerContactId
     *
     * @return cashAccount
     */
    public function setCustomerContactId($customerContactId)
    {
        $this->customer_contact_id = $customerContactId;

        return $this;
    }

    /**
     * @return int
     */
    public function getIsArchived()
    {
        return $this->is_archived;
    }

    /**
     * @param int $isArchived
     *
     * @return cashAccount
     */
    public function setIsArchived($isArchived)
    {
        $this->is_archived = $isArchived;

        return $this;
    }

    /**
     * @return int
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * @param int $sort
     *
     * @return cashAccount
     */
    public function setSort($sort)
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * @return bool
     */
    public function beforeSave()
    {
        $this->updateCreateUpdateDatetime();

        return true;
    }
}
