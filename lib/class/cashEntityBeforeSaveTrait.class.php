<?php

/**
 * Trait cashEntityBeforeSaveTrait
 */
trait cashEntityBeforeSaveTrait
{
    protected function updateCreateUpdateDatetime()
    {
        if (!$this->id) {
            $this->createDatetime = date('Y-m-d H:i:s');
        } else {
            $this->updateDatetime = date('Y-m-d H:i:s');
        }
    }
}
