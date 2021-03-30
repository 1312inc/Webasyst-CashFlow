<?php

trait cashTinkoffPluginToArrayTrait
{
    public function toArray(): array
    {
        $data = [];
        foreach (get_object_vars($this) as $name => $val) {
            if (is_object($val) && $val instanceof cashTinkoffPluginToArrayInterface) {
                $val = $val->toArray();
            }
            $data[$name] = $val;
        }

        return $data;
    }
}
