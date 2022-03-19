<?php

final class cashReportDdsEntity
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
     * @var bool
     */
    private $isChild;

    public function __construct(
        string $name,
        string $id,
        bool $expense = false,
        bool $income = false,
        string $icon = '',
        bool $isHeader = false,
        ?string $color = null,
        bool $isChild = false
    ) {
        $this->name = $name;
        $this->id = $id;
        $this->icon = $icon;
        $this->isHeader = $isHeader;
        $this->expense = $expense;
        $this->income = $income;
        $this->color = $color;
        $this->isChild = $isChild;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function isHeader(): bool
    {
        return $this->isHeader;
    }

    public function isExpense(): bool
    {
        return $this->expense;
    }

    public function isIncome(): bool
    {
        return $this->income;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function isChild(): bool
    {
        return $this->isChild;
    }
}
