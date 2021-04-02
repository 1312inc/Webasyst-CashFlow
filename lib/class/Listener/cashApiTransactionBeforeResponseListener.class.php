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
        foreach ($handlers as $handler) {
            if (!is_object($handler) || !$handler instanceof cashEventApiTransactionExternalInfoHandlerInterface) {
                continue;
            }

            $registry->add($handler->getSource(), $handler);
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

    public function getExternalInfoHandler(): cashEventApiTransactionExternalInfoShopHandler
    {
        return new cashEventApiTransactionExternalInfoShopHandler();
    }
}
