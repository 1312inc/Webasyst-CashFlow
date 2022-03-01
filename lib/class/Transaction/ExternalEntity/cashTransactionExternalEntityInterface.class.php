<?php

/**
 * Interface cashTransactionExternalEntityInterface
 */
interface cashTransactionExternalEntityInterface
{
    public function getHtml(): string;

    public function getIcon(): string;

    public function getLink(): string;

    public function getAppUrl(): ?string;

    public function getId(): ?int;

    public function getAppIcon(): ?string;

    public function getAppName(): ?string;

    public function getEntityName(): ?string;

    public function isSelfDestructWhenDue(): bool;
}
