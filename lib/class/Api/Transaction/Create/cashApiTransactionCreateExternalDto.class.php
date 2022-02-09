<?php

class cashApiTransactionCreateExternalDto
{
    /**
     * @var string
     */
    private $source;

    /**
     * @var string
     */
    private $id;

    /**
     * @var array
     */
    private $data;

    public function __construct(string $externalSource, string $externalId, array $externalData)
    {
        $this->source = $externalSource;
        $this->id = $externalId;
        $this->data = $externalData;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public static function fromArray(array $data): self
    {
        if (empty($data['id'])) {
            throw new cashValidateException('Missing external id');
        }

        if (empty($data['source'])) {
            throw new cashValidateException('Missing external source');
        }

        return new self($data['source'], $data['id'], $data['data'] ?? []);
    }
}
