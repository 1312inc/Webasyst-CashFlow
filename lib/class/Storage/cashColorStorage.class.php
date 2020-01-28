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
            '#880E4F',
            '#AD1457',
            '#C2185B',
            '#D81B60',
            '#E91E63',
            '#EC407A',
            '#F06292',
            '#F48FB1',
            '#F8BBD0',

            '#B71C1C',
            '#C62828',
            '#D32F2F',
            '#E53935',
            '#F44336',
            '#EF5350',
            '#E57373',
            '#EF9A9A',
            '#FFCDD2',

            '#BF360C',
            '#D84315',
            '#E64A19',
            '#F4511E',
            '#FF5722',
            '#FF7043',
            '#FF8A65',
            '#FFAB91',
            '#FFCCBC',

            '#E65100',
            '#EF6C00',
            '#F57C00',
            '#FB8C00',
            '#FF9800',
            '#FFA726',
            '#FFB74D',
            '#FFCC80',
            '#FFE0B2',
        ];
    }

    /**
     * @return array
     */
    public static function getIncomeColors()
    {
        return [
            '#004D40',
            '#00695C',
            '#00796B',
            '#00897B',
            '#009688',
            '#26A69A',
            '#4DB6AC',
            '#80CBC4',
            '#B2DFDB',

            '#1B5E20',
            '#2E7D32',
            '#388E3C',
            '#43A047',
            '#4CAF50',
            '#66BB6A',
            '#81C784',
            '#A5D6A7',
            '#C8E6C9',

            '#33691E',
            '#558B2F',
            '#689F38',
            '#7CB342',
            '#8BC34A',
            '#9CCC65',
            '#AED581',
            '#C5E1A5',
            '#DCEDC8',

            '#827717',
            '#9E9D24',
            '#AFB42B',
            '#C0CA33',
            '#CDDC39',
            '#D4E157',
            '#DCE775',
            '#E6EE9C',
            '#F0F4C3'

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
