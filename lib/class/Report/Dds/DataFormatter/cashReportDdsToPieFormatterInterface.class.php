<?php

/**
 * Interface cashReportDdsToPieFormatterInterface
 */
interface cashReportDdsToPieFormatterInterface
{
    /**
     * @param array $data
     *
     * @return JsonSerializable
     */
    public function format(array $data): JsonSerializable;
}
