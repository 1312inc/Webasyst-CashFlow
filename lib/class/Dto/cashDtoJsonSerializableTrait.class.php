<?php

/**
 * Trait cashDtoJsonSerializableTrait
 */
trait cashDtoJsonSerializableTrait
{
    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $propToJson = property_exists($this, 'jsonSerializableProperties')
            ? $this->jsonSerializableProperties
            : get_object_vars($this);

        $jsonData = [];
        foreach ($propToJson as $name => $value) {
            $jsonData[$name] = $this->$name;
        }

        return $jsonData;
    }
}
