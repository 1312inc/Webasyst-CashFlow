<?php

/**
 * Class cashColorStorage
 */
final class cashColorStorage
{
    const DEFAULT_NO_CATEGORY_GRAPH_COLOR = '#00ffff';
    const DEFAULT_ACCOUNT_GRAPH_COLOR     = '#0000ff';

    /**
     * @return array
     */
    public static function getExpenseColors()
    {
        return [
            '#aa0000',
            '#cc0000',
        ];
    }

    /**
     * @return array
     */
    public static function getIncomeColors()
    {
        return [
            '#00aa00',
            '#00cc00',
        ];
    }

    /**
     * @param string $type
     *
     * @return array
     */
    public static function getColorsByType($type)
    {
        if ($type === cashCategory::TYPE_INCOME) {
            return self::getIncomeColors();
        }

        if ($type === cashCategory::TYPE_EXPENSE) {
            return self::getExpenseColors();
        }

        return array_merge(self::getIncomeColors(), self::getExpenseColors());
    }
}
