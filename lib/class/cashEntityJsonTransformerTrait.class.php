<?php

/**
 * Trait cashEntityJsonTransformerTrait
 */
trait cashEntityJsonTransformerTrait
{
    /**
     * @param array $properties
     */
    private function fromJson(array $properties)
    {
        foreach ($properties as $property) {
            if (!is_array($this->$property)) {
                $this->$property = empty($this->$property)
                    ? []
                    : json_decode($this->$property, true);
            }
        }
    }

    /**
     * @param array $properties
     */
    private function toJson(array $properties)
    {
        foreach ($properties as $property) {
            if (is_array($this->$property)) {
                $this->$property = empty($this->$property)
                    ? null
                    : json_encode(
                        $this->$property,
                        JSON_UNESCAPED_UNICODE
                    );
            }
        }
    }
}
