<?php

/**
 * Trait cashDtoJsonSerializableTrait
 */
trait cashDtoJsonSerializableTrait
{
    /**
     * @return array
     */
    #[ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $propToJson = property_exists($this, 'jsonSerializableProperties')
            ? $this->jsonSerializableProperties
            : array_keys(get_object_vars($this));

        $jsonData = [];
        foreach ($propToJson as $name) {
            $jsonData[$name] = $this->$name;
        }

        return $jsonData;
    }
}
