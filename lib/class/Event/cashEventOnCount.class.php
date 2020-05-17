<?php

/**
 * Class cashEventOnCount
 */
class cashEventOnCount extends cashEvent
{
    private $idle = false;

    /**
     * cashEventOnCount constructor.
     *
     * @param bool $idle
     */
    public function __construct($idle = false)
    {
        parent::__construct(cashEventStorage::ON_COUNT);

        $this->idle = $idle;
    }

    /**
     * @return bool
     */
    public function isIdle()
    {
        return $this->idle;
    }
}
