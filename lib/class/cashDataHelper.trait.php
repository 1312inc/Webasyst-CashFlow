<?php

trait cashDataHelperTrait
{
    /**
     * @param $sql_dt
     * @param $tz
     * @return string|null
     */
    protected function formatDatetimeToISO8601($sql_dt, $tz = 'UTC')
    {
        return cashHelper::convertDateToISO8601($sql_dt, $tz);
    }

    /**
     * @param array $data
     * @param array $fields
     * @param array $field_types
     * @return array
     */
    protected function singleFilterFields($data, array $fields, array $field_types = [])
    {
        $res = [];
        foreach (array_keys($data) as $key) {
            if (in_array($key, $fields)) {
                if (!isset($field_types[$key]) || $data[$key] === null) {
                    $res[$key] = $data[$key];
                    continue;
                }
                if ($field_types[$key] === 'int') {
                    $res[$key] = intval($data[$key]);
                } elseif ($field_types[$key] === 'bool') {
                    $res[$key] = boolval($data[$key]);
                } elseif ($field_types[$key] === 'float') {
                    $res[$key] = floatval($data[$key]);
                } elseif ($field_types[$key] === 'double') {
                    $res[$key] = doubleval($data[$key]);
                } elseif ($field_types[$key] === 'datetime') {
                    $res[$key] = $this->formatDatetimeToISO8601($data[$key]);
                } elseif ($field_types[$key] === 'dateiso') {
                    $res[$key] = $this->formatDatetimeToISO8601($data[$key], null);
                } else {
                    $res[$key] = $data[$key];
                }
            }
        }

        return $res;
    }

    /**
     * @param $data
     * @param array $fields
     * @param array $field_types
     * @return array
     */
    protected function filterFields($data, array $fields, array $field_types = [])
    {
        if (!empty($data) && is_array($data)) {
            return array_map(function ($el) use ($fields, $field_types) {
                return $this->singleFilterFields($el, $fields, $field_types);
            }, array_values($data));
        }

        return [];
    }
}
