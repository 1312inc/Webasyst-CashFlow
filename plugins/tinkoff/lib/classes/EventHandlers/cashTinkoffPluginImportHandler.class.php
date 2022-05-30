<?php

final class cashTinkoffPluginImportHandler implements cashImportHandlerInterface
{
    public const IDENTIFIER = 'tinkoff';

    public function canHandle(string $identifier): bool
    {
        return $identifier === self::IDENTIFIER;
    }

    public function handle(array $params): string
    {
        return 'tinkoff business';
    }
}
