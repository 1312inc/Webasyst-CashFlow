<?php

final class cashTinkoffPluginImportHandlerListener
{
    /**
     * @return cashImportHandlerInterface[]
     */
    public function handle(cashImportHandlerParamsEvent $event): array
    {
        return [new cashTinkoffPluginImportHandler()];
    }
}
