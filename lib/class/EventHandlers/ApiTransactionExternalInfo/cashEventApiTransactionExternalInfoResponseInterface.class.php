<?php

interface cashEventApiTransactionExternalInfoResponseInterface extends JsonSerializable
{
    public function getColor(): string;

    public function getName(): string;
}
