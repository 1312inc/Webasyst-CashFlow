<?php

/**
 * Class kmwaAssert
 */
class kmwaAssert
{
    /**
     * @param $object
     * @param $class
     *
     * @throws kmwaAssertException
     */
    public static function instance($object, $class)
    {
        $type = gettype($object);
        if ($type !== 'object') {
            throw new kmwaAssertException(sprintf('Expected %s. Got %s', $class, $type));
        }

        if (!$object instanceof $class) {
            throw new kmwaAssertException(sprintf('Expected %s. Got %s', $class, get_class($object)));
        }
    }

    /**
     * @param $variable
     * @param $value
     *
     * @throws kmwaAssertException
     */
    public static function gt($variable, $value)
    {
        if ($variable <= $value) {
            throw new kmwaAssertException(sprintf('Variable %s should be greater then %s', $variable, $value));
        }
    }

    /**
     * @param $variable
     * @param $value
     *
     * @throws kmwaAssertException
     */
    public static function gte($variable, $value)
    {
        if ($variable < $value) {
            throw new kmwaAssertException(sprintf('Variable %s should be greater or equal then %s', $variable, $value));
        }
    }
}
