<?php

/**
 * Class cashEntitySaver
 */
abstract class cashEntitySaver
{
    /**
     * @var string
     */
    protected $error = '';

    /**
     * @param array $data
     *
     * @return bool|cashAbstractEntity
     */
    abstract public function saveFromArray(array $data);

    /**
     * @param array $data
     *
     * @return bool
     */
    abstract public function validate(array $data);

    /**
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }
}
