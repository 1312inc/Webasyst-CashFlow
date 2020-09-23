<?php

/**
 * Class cashReportDdsEntity
 */
class cashReportDdsEntity
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $icon;

    /**
     * @var bool
     */
    private $isHeader;

    /**
     * @var bool
     */
    private $expense = false;

    /**
     * @var bool
     */
    private $income = false;

    /**
     * @var null|string
     */
    private $color;

    /**
     * cashReportDdsEntity constructor.
     *
     * @param        $name
     * @param        $id
     * @param bool   $expense
     * @param bool   $income
     * @param string $icon
     * @param bool   $isHeader
     * @param null   $color
     */
    public function __construct(
        $name,
        $id,
        $expense = false,
        $income = false,
        $icon = '',
        $isHeader = false,
        $color = null
    ) {
        $this->name = $name;
        $this->id = $id;
        $this->icon = $icon;
        $this->isHeader = $isHeader;
        $this->expense = $expense;
        $this->income = $income;
        $this->color = $color;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return $this->icon;
    }

    /**
     * @return bool
     */
    public function isHeader(): bool
    {
        return $this->isHeader;
    }

    /**
     * @return bool
     */
    public function isExpense(): bool
    {
        return $this->expense;
    }

    /**
     * @return bool
     */
    public function isIncome(): bool
    {
        return $this->income;
    }

    /**
     * @return string|null
     */
    public function getColor(): ?string
    {
        return $this->color;
    }
}
