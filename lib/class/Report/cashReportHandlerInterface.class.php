<?php

interface cashReportHandlerInterface
{
    /**
     * Может ли обработать отчет такого типа
     */
    public function canHandle(string $identifier): bool;

    public function handle(array $params): string;
}
