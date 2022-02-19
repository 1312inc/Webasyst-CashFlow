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
    private $id;

    /**
     * @var string
     */
    private $glyph;

    /**
     * @var ?string
     */
    private $entityUrl;

    /**
     * @var ?string
     */
    private $entityIcon;

    /**
     * @var ?string
     */
    private $entityName;

    public function __construct(
        int $id,
        string $color,
        string $name,
        string $glyph = '',
        ?string $entityUrl = null,
        ?string $entityIcon = null,
        ?string $entityName = null
    ) {
        $this->color = $color;
        $this->name = $name;
        $this->glyph = $glyph;
        $this->entityUrl = $entityUrl;
        $this->entityIcon = $entityIcon;
        $this->entityName = $entityName;
        $this->id = $id;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'color' => $this->color,
            'glyph' => $this->glyph,
            'entity_icon' => $this->entityIcon,
            'entity_url' => $this->entityUrl,
            'entity_name' => $this->entityName,
        ];
    }
}
