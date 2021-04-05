<?php

interface cashEventApiTransactionExternalInfoResponseInterface extends JsonSerializable
{
    public function getColor(): string;

    public function getName(): string;

    public function getGlyph(): string;

    public function getUrl(): string;

    public function getIcon(): string;
}
