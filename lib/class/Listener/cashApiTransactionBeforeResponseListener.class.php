<?php

final class cashApiTransactionBeforeResponseListener
{
    public function updateExternalInfo(cashEvent $event): void
    {
        /** @var ArrayIterator $response */
        $response = $event->getObject();

        $registry = new cashEventApiTransactionExternalInfoHandlersRegistry();
        $handlers = cash()->waDispatchEvent(
            new cashEventApiTransactionExternalInfo(cashEventStorage::API_TRANSACTION_RESPONSE_EXTERNAL_DATA)
        );
        foreach ($handlers as $infoHandlers) {
            if (!is_array($infoHandlers)) {
                $infoHandlers = [$infoHandlers];
            }

            foreach ($infoHandlers as $handler) {
                if (!$handler instanceof cashEventApiTransactionExternalInfoHandlerInterface) {
                    continue;
                }

                $registry->add($handler);
            }
        }

        array_map(
            static function (cashApiTransactionResponseDto $cashTransactionDto) use ($registry) {
                if ($cashTransactionDto->external_source && $registry->has($cashTransactionDto->external_source)) {
                    $cashTransactionDto->external_source_info = $registry->get($cashTransactionDto->external_source)
                        ->getResponse($cashTransactionDto);
                }
            },
            iterator_to_array($response)
        );
    }

    /**
     * @return array<cashEventApiTransactionExternalInfoHandlerInterface>
     */
    public function getExternalInfoHandlers(): array
    {
        return [
            new cashEventApiTransactionExternalInfoShopHandler(),
            new cashEventApiTransactionExternalInfoCsvHandler(),
        ];
    }
}
