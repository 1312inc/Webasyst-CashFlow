<?php


class cashCategoryDto extends cashAbstractDto
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $slug;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $color;

    /**
     * @var int
     */
    public $sort;

    /**
     * @var string
     */
    public $createDatetime;

    /**
     * cashCategoryDto constructor.
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        if ($data) {
            $this->initializeWithArray($data);
        } else {
            $this->name = _w('New category');
        }
    }
}