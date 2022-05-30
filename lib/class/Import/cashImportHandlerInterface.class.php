<?php

interface cashImportHandlerInterface
{
    /**
     * Может ли обработать импорт такого типа
     */
    public function canHandle(string $identifier): bool;

    public function handle(array $params): string;
}
