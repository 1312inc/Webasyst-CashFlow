<?php

interface cashImportMenuItemInterface
{
    public function getIdentifier(): string;
    public function getUrl(): ?string;
    public function getAnchor(): string;
}
