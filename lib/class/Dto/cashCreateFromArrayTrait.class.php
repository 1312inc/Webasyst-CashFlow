<?php

/**
 * Trait cashCreateFromArrayTrait
 */
trait cashCreateFromArrayTrait
{
    /**
     * @param array $data
     */
    protected function initializeWithArray(array $data)
    {
        foreach (get_object_vars($this) as $name => $value) {
            if (array_key_exists($name, $data)) {
                $this->{$name} = $data[$name];
            }
        }
    }
}
