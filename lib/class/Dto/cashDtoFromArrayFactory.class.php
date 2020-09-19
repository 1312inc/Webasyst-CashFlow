<?php

/**
 * Class cashDtoFromArrayFactory
 */
final class cashDtoFromArrayFactory
{
    /**
     * @param object $dto
     * @param array  $data
     *
     * @return cashAbstractDto
     */
    public static function fromArray($dto, array $data)
    {
        foreach (get_object_vars($dto) as $name => $value) {
            if (array_key_exists($name, $data)) {
                $dto->{$name} = $data[$name];
            }
        }

        return $dto;
    }
}
