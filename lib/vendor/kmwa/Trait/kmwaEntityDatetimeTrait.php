<?php

/**
 * Trait kmwaEntityDatetimeTrait
 */
trait kmwaEntityDatetimeTrait
{
    /**
     * @var string|DateTime
     */
    private $create_datetime;

    /**
     * @var string|DateTime|null
     */
    private $update_datetime;

    /**
     * @return DateTime|string
     */
    public function getCreateDatetime()
    {
        return $this->create_datetime;
    }

    /**
     * @param DateTime|string $createDatetime
     *
     * @return static
     */
    public function setCreateDatetime($createDatetime)
    {
        $this->create_datetime = $createDatetime;

        return $this;
    }

    /**
     * @return DateTime|string|null
     */
    public function getUpdateDatetime()
    {
        return $this->update_datetime;
    }

    /**
     * @param DateTime|string|null $updateDatetime
     *
     * @return static
     */
    public function setUpdateDatetime($updateDatetime)
    {
        $this->update_datetime = $updateDatetime;

        return $this;
    }

    protected function updateCreateUpdateDatetime()
    {
        if (!$this->id) {
            $this->create_datetime = date('Y-m-d H:i:s');
        } else {
            $this->update_datetime = date('Y-m-d H:i:s');
        }
    }
}
