<?php

final class cashTinkoffPluginToken
{
    /**
     * @var string
     */
    private $value;

    public function __construct(string $token)
    {
        $this->value = $token;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
