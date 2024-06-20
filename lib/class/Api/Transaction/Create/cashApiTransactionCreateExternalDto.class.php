<?php

class cashApiTransactionCreateExternalDto
{
    /**
     * @var string
     */
    private $source;

    /**
     * @var int|null
     */
    private $id;

    /**
     * @var array
     */
    private $data;

    public function __construct(string $externalSource, ?int $externalId, array $externalData)
    {
        $this->source = $externalSource;
        $this->id = $externalId;
        $this->data = $externalData;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public static function fromArray(array $data): self
    {
        $data += [
            'id' => null,
            'source' => '',
            'data' => []
        ];

        return new self((string) $data['source'], $data['id'], (array) $data['data']);
    }
}
