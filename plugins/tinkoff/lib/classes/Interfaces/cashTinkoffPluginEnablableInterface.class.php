<?php

interface cashTinkoffPluginEnablableInterface
{
    public function isEnabled(): bool;

    public function setEnabled(bool $enabled);
}
