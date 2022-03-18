<?php

interface cashDdsPluginToPieFormatterInterface
{
    /**
     * @param array $data
     *
     * @return JsonSerializable
     */
    public function format(array $data): JsonSerializable;
}
