<?php

/**
 * Class cashColorStorage
 */
final class cashColorStorage
{
    const DEFAULT_ACCOUNT_GRAPH_COLOR = '#ffd700';
    const TRANSFER_CATEGORY_COLOR = '#8bd5ff';

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

            '#CC1010',
            '#EE2222',
            '#FF1312',
            '#FF0000',
            '#FF5722',
            '#FF7043',
            '#FF8A65',
            '#FFAB91',
            '#FFCCBC',

            '#C65100',
            '#EF6C00',
            '#F57C00',
            '#FB8C20',
            '#FFA853',
            '#FFBB77',
            '#FFCC88',
            '#FFDD99',
            '#FFEEAA',

        ];
    }

    /**
     * @return array
     */
    public static function getIncomeColors()
    {
        return [
            '#ccff99',
            '#99ff66',
            '#66ff66',
            '#66ff99',
            '#66ffcc',
            '#66ffff',
            '#66ccff',
            '#6699ff',
            '#6666ff',

            '#ccff00',
            '#66ff00',
            '#00ff00',
            '#00ff66',
            '#00ff99',
            '#00ffff',
            '#00ccff',
            '#0099ff',
            '#0033ff',

            '#99cc00',
            '#33cc00',
            '#00cc00',
            '#00cc33',
            '#00cc66',
            '#00cccc',
            '#0099cc',
            '#0066cc',
            '#0033cc',

            '#669900',
            '#339900',
            '#009900',
            '#009933',
            '#009966',
            '#009999',
            '#006699',
            '#003399',
            '#000099',

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
