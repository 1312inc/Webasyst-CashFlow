<?php

namespace ApiPack1312;

use ApiPack1312\Exception\ApiCastParamException;
use DateTimeImmutable;

class ApiParamsCaster
{
    public const CAST_INT         = 'int';
    public const CAST_FLOAT       = 'float';
    public const CAST_ARRAY       = 'array';
    public const CAST_STRING      = 'string';
    public const CAST_STRING_TRIM = 'string_trim';
    public const CAST_DATETIME    = 'datetime';
    public const CAST_ENUM        = 'enum';
    public const CAST_BOOLEAN     = 'boolean';

    /**
     * @param             $var
     * @param string|null $type
     * @param mixed       $format
     *
     * @return array|DateTimeImmutable|float|int|string
     * @throws ApiCastParamException
     */
    public function cast($var, ?string $type, $format = null)
    {
        switch ($type) {
            case self::CAST_INT:
                return (int) $var;

            case self::CAST_FLOAT:
                return (float) $var;

            case self::CAST_BOOLEAN:
                return filter_var($var, FILTER_VALIDATE_BOOLEAN);

            case self::CAST_ARRAY:
                return (array) $var;

            case self::CAST_STRING_TRIM:
                return trim((string) $var);

            case self::CAST_DATETIME:
                $var = DateTimeImmutable::createFromFormat($format, $var);
                if ($var === false) {
                    throw new ApiCastParamException(sprintf('Wrong format "%s" for value "%s"', $format, $var));
                }

                return $var;

            case self::CAST_ENUM:
                if (is_array($format) && !in_array($var, $format, true)) {
                    throw new ApiCastParamException(
                        sprintf('Wrong value "%s". Expected one of "%s"', $var, implode(', ', $format))
                    );
                }
                break;

            case self::CAST_STRING:
                return (string) $var;

            default:
                return $var;
        }

        return $var;
    }
}
