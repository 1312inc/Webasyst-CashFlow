<?php

/**
 * Trait cashEntityBeforeSaveTrait
 */
trait cashEntityBeforeSaveTrait
{
    protected function updateCreateUpdateDatetime()
    {
        if (!$this->id) {
            $this->create_datetime = date('Y-m-d H:i:s');
        } else {
            $this->update_datetime = date('Y-m-d H:i:s');
        }
    }
}
