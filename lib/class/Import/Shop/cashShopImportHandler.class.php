<?php

final class cashShopImportHandler implements cashImportHandlerInterface
{
    public function canHandle(string $identifier): bool
    {
        return $identifier === 'import/shop';
    }

    public function handle(array $params): string
    {
        return 'отдельная страница, если вы видите это сообщение - это баг';
    }
}
