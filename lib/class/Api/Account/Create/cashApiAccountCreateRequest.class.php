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
     * @var int
     */
    private $is_imaginary;

    /**
     * @var string
     */
    private $description;

    public function __construct(string $name, string $currency, ?string $icon, int $is_imaginary, ?string $description)
    {
        $name = trim($name);
        if (empty($name)) {
            throw new cashValidateException(_w('No account name'));
        } elseif (empty($currency)) {
            throw new cashValidateException(_w('No account currency'));
        } elseif (!empty($icon)) {
            if (!preg_match('#^(https?://)?(www\.)?.{2,225}\..{2,20}.+$#u', $icon)) {
                $icon = '';
            }
        }

        $this->name = $name;
        $this->currency = $currency;
        $this->icon = (string) $icon;
        $this->is_imaginary = $is_imaginary;
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

    public function getImaginary(): int
    {
        return $this->is_imaginary;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}
