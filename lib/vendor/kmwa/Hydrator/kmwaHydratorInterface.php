<?php

/**
 * Interface kmwaHydratorInterface
 */
interface kmwaHydratorInterface
{
    /**
     * @param kmwaHydratableInterface $object
     * @param array                   $fields
     * @param array                   $dbFields
     *
     * @return array
     */
    public function extract(kmwaHydratableInterface $object, array $fields = [], $dbFields = []);

    /**
     * @param kmwaHydratableInterface $object
     * @param array                   $data
     *
     * @return object
     */
    public function hydrate(kmwaHydratableInterface $object, array $data);
}
