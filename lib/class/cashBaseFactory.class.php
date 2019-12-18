<?php

/**
 * Class cashBaseFactory
 */
class cashBaseFactory
{
    /**
     * @var string
     */
    protected $entity;

    /**
     * @return mixed
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @param string $entity
     *
     * @return cashBaseFactory
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * @return cashAbstractEntity
     */
    public function createNew()
    {
        $entity = $this->getEntity();

        return new $entity();
    }

    /**
     * @param array $data
     *
     * @return cashAbstractEntity|object
     */
    public function createNewWithData(array $data)
    {
        $entity = $this->createNew();

        return cash()->getHydrator()->hydrate($entity, $data);
    }
}
