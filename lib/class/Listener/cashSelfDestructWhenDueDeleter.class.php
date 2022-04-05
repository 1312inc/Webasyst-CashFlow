<?php

final class cashSelfDestructWhenDueDeleter extends waEventHandler
{
    /**
     * @throws waException
     */
    public function onCount(cashEventOnCount $event): void
    {
        cash()->getModel(cashTransaction::class)
            ->deleteSelfDestructWhenDueBeforeDate(new DateTimeImmutable());
    }
}
