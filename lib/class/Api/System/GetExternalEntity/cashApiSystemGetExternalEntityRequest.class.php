<?php

final class cashApiSystemGetExternalEntityRequest
{
    /**
     * @var string
     */
    private $source;

    /**
     * @var string
     */
    private $id;

    public function __construct(string $source, string $id)
    {
        $this->source = $source;
        $this->id = $id;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function getId(): string
    {
        return $this->id;
    }
}
