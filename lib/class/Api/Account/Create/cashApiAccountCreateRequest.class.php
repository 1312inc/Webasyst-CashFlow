<?php

class cashApiAccountCreateRequest
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
    private $description;

    public function __construct(string $name, string $currency, string $icon, ?string $iconLink, ?string $description)
    {
        if (empty($name)) {
            throw new cashValidateException(_w('No account name'));
        }

        $name = trim($name);

        if (empty($currency)) {
            throw new cashValidateException(w('No account currency'));
        }

        if (!empty($iconLink) && preg_match('~https?://.{2,225}\..{2,20}~', $iconLink)) {
            $icon = $iconLink;
        }

        $this->name = $name;
        $this->currency = $currency;
        $this->icon = $icon;
        $this->description = (string) $description;
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

    public function getDescription(): string
    {
        return $this->description;
    }
}
