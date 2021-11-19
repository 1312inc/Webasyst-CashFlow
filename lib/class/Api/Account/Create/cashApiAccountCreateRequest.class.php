<?php

final class cashApiAccountCreateRequest
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $currency;

    /**
     * @var string
     */
    private $icon;

    /**
     * @var string
     */
    private $iconLink;

    /**
     * @var string
     */
    private $description;

    public function __construct(string $name, string $currency, string $icon, string $iconLink, string $description)
    {
        $this->name = $name;
        $this->currency = $currency;
        $this->icon = $icon;
        $this->iconLink = $iconLink;
        $this->description = $description;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function getIconLink(): string
    {
        return $this->iconLink;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}
