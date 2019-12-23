<?php

/**
 * Interface cashFromEntityCreatableInterface
 */
interface cashFromEntityCreatableInterface
{
    /**
     * @param kmwaHydratableInterface $entity
     *
     * @return cashAbstractDto
     */
    public static function createFromEntity(kmwaHydratableInterface $entity);

    /**
     * @param kmwaHydratableInterface[] $entities
     *
     * @return cashAbstractDto[]
     */
    public static function createFromEntities(array $entities);
}
