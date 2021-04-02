<?php

class cashEventApiTransactionExternalInfoResponse implements cashEventApiTransactionExternalInfoResponseInterface
{
    /**
     * @var string
     */
    private $color;

    /**
     * @var string
     */
    private $name;

    public function __construct(string $color, string $name)
    {
        $this->color = $color;
        $this->name = $name;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function jsonSerialize()
    {
        return [
            'name' => $this->name,
            'color' => $this->color,
        ];
    }
}
