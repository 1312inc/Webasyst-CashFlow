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

    /**
     * @var string
     */
    private $glyph;

    public function __construct(string $color, string $name, string $glyph)
    {
        $this->color = $color;
        $this->name = $name;
        $this->glyph = $glyph;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getGlyph(): string
    {
        return $this->glyph;
    }

    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name,
            'color' => $this->color,
            'glyph' => $this->glyph,
        ];
    }
}
