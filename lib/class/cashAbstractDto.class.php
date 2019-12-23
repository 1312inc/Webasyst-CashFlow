<?php

/**
 * Class cashAbstractDto
 */
class cashAbstractDto implements cashFromEntityCreatableInterface, JsonSerializable
{
    /**
     * Specify data which should be serialized to JSON
     *
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        $data = [];
        foreach (get_object_vars($this) as $name => $value) {
            $data[$name] = $this->{$name};
        }

        return $data;
    }

    /**
     * @param kmwaHydratableInterface $entity
     *
     * @return cashAbstractDto
     */
    public static function createFromEntity(kmwaHydratableInterface $entity)
    {
        $dto = new static();
        $array = cash()->getHydrator()->extract($entity);

        return static::createFromArray($dto, $array);
    }

    /**
     * @param kmwaHydratableInterface[] $entities
     *
     * @return cashAbstractDto[]
     */
    public static function createFromEntities(array $entities)
    {
        $dtos = [];

        foreach ($entities as $entity) {
            $dtos[] = static::createFromEntity($entity);
        }

        return $dtos;
    }

    /**
     * @param cashAbstractDto $dto
     * @param array           $data
     *
     * @return cashAbstractDto
     */
    public static function createFromArray($dto, array $data)
    {
        foreach (get_object_vars($dto) as $name => $value) {
            if (array_key_exists($name, $data)) {
                $dto->{$name} = $data[$name];
            }
        }

        return $dto;
    }
}
