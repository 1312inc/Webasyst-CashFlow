<?php

final class cashCsvImportHandler implements cashImportHandlerInterface
{
    public const IDENTIFIER = 'csv';

    public function canHandle(string $identifier): bool
    {
        return $identifier === self::IDENTIFIER;
    }

    public function handle(array $params): string
    {
        return wa()->getView()->renderTemplate(
            wa()->getAppPath('templates/actions/import/csv/csv.html'),
            [],
            true
        );
    }
}
