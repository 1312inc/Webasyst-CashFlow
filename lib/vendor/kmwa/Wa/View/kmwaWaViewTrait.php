<?php

/**
 * Trait kmwaWaViewTrait
 */
trait kmwaWaViewTrait
{
    /**
     * @var array
     */
    protected $idNamesFromRequest = ['id'];

    /**
     * @param int $id
     *
     * @return int|mixed
     * @throws kmwaNotFoundException
     */
    protected function getId($id = 0)
    {
        foreach ($this->idNamesFromRequest as $idName) {
            $id = $id ?: waRequest::request($idName, 0, waRequest::TYPE_INT);
            if ($id) {
                break;
            }
        }

        if (!$id) {
            throw new kmwaNotFoundException();
        }

        return $id;
    }
}
