<?php

final class cashApiCategorySortRequest
{
    private $order;

    public function __construct(array $order)
    {
        $this->order = $order;
    }

    public function getOrder(): array
    {
        return $this->order;
    }
}
