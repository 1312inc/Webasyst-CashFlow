<?php

/**
 * Class cashColorStorage
 */
final class cashColorStorage
{
    const DEFAULT_NO_CATEGORY_GRAPH_COLOR = '#dddddd';
    const DEFAULT_ACCOUNT_GRAPH_COLOR     = '#ffd700';

    /**
     * @return array
     */
    public static function getExpenseColors()
    {
        return [
            '#751208',
            '#9c2100',
            '#bb2800',
            '#cd3a00',
            '#e55c00',
            '#f17827',
            '#ff893b',
            '#ffb164'
        ];
    }

    /**
     * @return array
     */
    public static function getIncomeColors()
    {
        return [
            '#175601',
            '#006a07',
            '#009442',
            '#07b856',
            '#19cf6a',
            '#2fe681',
            '#64ffa9',
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
