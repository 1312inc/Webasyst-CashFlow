<?php

/**
 * Interface kmwaHydratableInterface
 */
interface kmwaHydratableInterface
{
    /**
     * @param array $data
     *
     * @return mixed
     */
    public function afterHydrate($data = []);

    /**
     * @param array $fields
     *
     * @return array
     */
    public function beforeExtract(array &$fields);

    /**
     * @param array $fields
     *
     * @return array
     */
    public function afterExtract(array &$fields);
}
