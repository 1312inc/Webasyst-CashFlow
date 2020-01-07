<?php

/**
 * Class cashDtoFromEntityFactory
 */
final class cashDtoFromEntityFactory
{
    /**
     * @param string $dtoClass
     * @param cashAbstractEntity $entity
     *
     * @return cashAbstractDto
     */
    public static function fromEntity($dtoClass, cashAbstractEntity $entity)
    {
        $array = cash()->getHydrator()->extract($entity);

        return new $dtoClass($array);
    }

    /**
     * @param string $dtoClass
     * @param cashAbstractEntity[] $entities
     *
     * @return cashAbstractDto[]
     */
    public static function fromEntities($dtoClass, array $entities)
    {
        $dtos = [];

        foreach ($entities as $entity) {
            $dtos[$entity->getId()] = static::fromEntity($dtoClass, $entity);
        }

        return $dtos;
    }
}
