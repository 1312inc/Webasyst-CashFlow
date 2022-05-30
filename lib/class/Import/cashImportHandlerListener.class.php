<?php

final class cashImportHandlerListener
{
    /**
     * @return cashImportHandlerInterface[]
     */
    public function handle(cashImportHandlerParamsEvent $event): array
    {
        return [
            new cashCsvImportHandler(),
            new cashShopImportHandler(),
        ];
    }
}
