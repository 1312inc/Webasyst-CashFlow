<?php

/**
 * Class cashCategoryModel
 */
class cashCategoryModel extends cashModel
{
    protected $table = 'cash_category';

    /**
     * @param array $data
     * @param int   $type
     *
     * @return bool|int|resource
     */
    public function insert($data, $type = 0)
    {
        $data['slug'] = $this->getUniqueSlug($data['slug']);

        return parent::insert($data, $type);
    }

    /**
     * @param array|string $field
     * @param array|string $value
     * @param null         $data
     * @param null         $options
     * @param bool         $return_object
     *
     * @return bool|waDbResultUpdate|null
     *
     */
    public function updateByField($field, $value, $data = null, $options = null, $return_object = false)
    {
        if ($field === 'slug') {
            $value = $this->getUniqueSlug($value);
        } elseif (is_array($value) && isset($value['slug'])) {
            $value['slug'] = $this->getUniqueSlug($value['slug']);
        }

        return parent::updateByField(
            $field,
            $value,
            $data,
            $options,
            $return_object
        );
    }

    /**
     * @param string $type
     *
     * @return array
     */
    public function getByType($type)
    {
        return $this
            ->select('*')
            ->where('`type` = s:type', ['type' => $type])
            ->order('sort ASC, id DESC')
            ->fetchAll();
    }

    /**
     * @param string $slug
     *
     * @return string
     */
    private function getUniqueSlug($slug)
    {
        $existingSlug = $this->select('slug')->where('slug = s:slug', ['slug' => $slug])->fetchField();
        if ($existingSlug) {
            $index = 0;
            if (preg_match('/.+_(\d+)/ui', $existingSlug, $matches)) {
                $index = $matches[1];
            }
            $slug = $existingSlug.'_'. (++$index);
        }

        return $slug;
    }
}
