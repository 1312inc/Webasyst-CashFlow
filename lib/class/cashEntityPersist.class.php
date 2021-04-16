<?php

/**
 * Class cashEntityPersist
 */
class cashEntityPersist
{
    /**
     * @param cashAbstractEntity $entity
     * @param array                $fields
     * @param int                  $type
     *
     * @return bool
     * @throws waException
     */
    public function insert(
        cashAbstractEntity $entity,
        $fields = [],
        $type = waModel::INSERT_ON_DUPLICATE_KEY_UPDATE
    ) {
        if (!$entity->beforeSave()) {
            return false;
        }

        $model = cash()->getModel(get_class($entity));
        $data = cash()->getHydrator()->extract($entity, $fields, $model->getMetadata());

        unset($data['id']);

        $id = $model->insert($data, $type);

        if ($id) {
            if (method_exists($entity, 'setId')) {
                $entity->setId($id);
            }

            return true;
        }

        return false;
    }

    /**
     * @param cashAbstractEntity $entity
     *
     * @return bool
     * @throws waException
     */
    public function delete(cashAbstractEntity $entity)
    {
        if (method_exists($entity, 'getId')) {
            $model = cash()->getModel(get_class($entity));

            return $model->deleteById($entity->getId());
        }

        throw new waException('No id in entity');
    }

    /**
     * @param cashAbstractEntity $entity
     * @param array                $fields
     *
     * @return bool|waDbResultUpdate|null
     * @throws waException
     */
    public function update(cashAbstractEntity $entity, $fields = [])
    {
        if (method_exists($entity, 'getId')) {
            if (!$entity->beforeSave()) {
                return false;
            }

            $model = cash()->getModel(get_class($entity));
            $data = cash()->getHydrator()->extract(
                $entity,
                $fields,
                $model->getMetadata()
            );

            unset($data['id']);

            return $model->updateById($entity->getId(), $data);
        }

        throw new waException('No id in entity');
    }

    /**
     * @param cashAbstractEntity $entity
     * @param array                $fields
     *
     * @return bool|waDbResultUpdate|null
     * @throws waException
     */
    public function save(cashAbstractEntity $entity, $fields = [])
    {
        if (method_exists($entity, 'getId')) {
            if ($entity->getId()) {
                return $this->update($entity, $fields);
            }

            return $this->insert($entity, $fields);
        }

        throw new waException('No id in entity');
    }

}