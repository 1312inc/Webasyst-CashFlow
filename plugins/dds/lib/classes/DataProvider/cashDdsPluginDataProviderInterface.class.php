<?php

/**
 * Interface cashDdsPluginDataProviderInterface
 */
interface cashDdsPluginDataProviderInterface
{
    /**
     * @param cashDdsPluginPeriod $period
     *
     * @return cashDdsPluginStatDto[]
     */
    public function getDataForPeriod(cashDdsPluginPeriod $period): array;
}
