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
     * @param object $entity
     * @param array  $data
     * @param array  $params
     *
     * @return bool|cashAbstractEntity
     */
    abstract public function saveFromArray($entity, array $data, array $params = []);

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
