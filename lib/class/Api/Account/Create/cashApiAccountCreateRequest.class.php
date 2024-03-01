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

    public function __construct(string $name, string $currency, ?string $icon, ?string $description)
    {
        $name = trim($name);
        if (empty($name)) {
            throw new cashValidateException(_w('No account name'));
        } elseif (empty($currency)) {
            throw new cashValidateException(_w('No account currency'));
        } elseif (!empty($icon)) {
            if (!preg_match('#^(https?://)?(www\.)?.{2,225}\..{2,20}.+$#u', $icon)) {
                throw new cashValidateException(_w('Incorrect URL format'));
            }
        }

        $this->name = $name;
        $this->currency = $currency;
        $this->icon = (string) $icon;
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
