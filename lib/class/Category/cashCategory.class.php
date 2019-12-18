<?php

/**
 * Class cashCategory
 */
class cashCategory extends cashAbstractEntity
{
    use cashEntityBeforeSaveTrait;

    const TYPE_INCOME = 'income';
    const TYPE_EXPENSE = 'expense';
    const TYPE_TRANSFER = 'transfer';

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $slug;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $color;

    /**
     * @var int
     */
    private $sort;

    /**
     * @var string
     */
    private $createDatetime;

    /**
     * @var string|null
     */
    private $updateDatetime;

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
     * @return cashCategory
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     *
     * @return cashCategory
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

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
     * @return cashCategory
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return cashCategory
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @param string $color
     *
     * @return cashCategory
     */
    public function setColor($color)
    {
        $this->color = $color;

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
     * @return cashCategory
     */
    public function setSort($sort)
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * @return string
     */
    public function getCreateDatetime()
    {
        return $this->createDatetime;
    }

    /**
     * @param string $createDatetime
     *
     * @return cashCategory
     */
    public function setCreateDatetime($createDatetime)
    {
        $this->createDatetime = $createDatetime;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getUpdateDatetime()
    {
        return $this->updateDatetime;
    }

    /**
     * @param string|null $updateDatetime
     *
     * @return cashCategory
     */
    public function setUpdateDatetime($updateDatetime)
    {
        $this->updateDatetime = $updateDatetime;

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
