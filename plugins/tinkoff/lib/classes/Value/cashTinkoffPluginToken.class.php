<?php

final class cashTinkoffPluginToken implements cashTinkoffPluginToArrayInterface
{
    use cashTinkoffPluginToArrayTrait;

    /**
     * @var string
     */
    private $value;

    /**
     * @var bool
     */
    private $valid;

    public function __construct(string $token, bool $valid)
    {
        $this->value = $token;
        $this->valid = $valid;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function isValid(): bool
    {
        return $this->valid;
    }
}
