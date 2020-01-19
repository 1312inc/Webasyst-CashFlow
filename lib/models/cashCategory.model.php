<?php

/**
 * Class cashCategoryModel
 */
class cashCategoryModel extends cashModel
{
    protected $table = 'cash_category';

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
     * @return array
     */
    public function getAllActive()
    {
        return $this
            ->select('*')
            ->order('sort ASC, id DESC')
            ->fetchAll('id');
    }

    /**
     * @param string $slug
     *
     * @return string
     */
    public function getUniqueSlug($slug)
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
