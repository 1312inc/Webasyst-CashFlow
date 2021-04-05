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
    /**
     * @var string
     */
    private $url;
    /**
     * @var string
     */
    private $icon;

    public function __construct(string $color, string $name, string $glyph = '', string $url = '', string $icon = '')
    {
        $this->color = $color;
        $this->name = $name;
        $this->glyph = $glyph;
        $this->url = $url;
        $this->icon = $icon;
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

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name,
            'color' => $this->color,
            'glyph' => $this->glyph,
            'icon' => $this->icon,
            'url' => $this->url,
        ];
    }
}
