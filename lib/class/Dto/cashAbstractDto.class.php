<?php

/**
 * Class cashAbstractDto
 */
class cashAbstractDto implements JsonSerializable
{
    use cashCreateFromArrayTrait;

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
}
