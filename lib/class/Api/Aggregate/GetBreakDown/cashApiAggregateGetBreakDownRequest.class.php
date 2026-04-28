<?php

/**
 * Class cashApiAggregateGetBreakDownRequest
 */
final class cashApiAggregateGetBreakDownRequest
{
    /**
     * @var string|DateTimeImmutable
     */
    public $from = '';

    /**
     * @var string|DateTimeImmutable
     */
    public $to = '';

    /**
     * @var string
     */
    public $filter = '';

    /**
     * @var int
     */
    public $children_help_parents;
}
