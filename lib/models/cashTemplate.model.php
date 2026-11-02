<?php

/**
 * Class cashTemplateModel
 */
class cashTemplateModel extends cashModel
{
    protected $table = 'cash_template';

    public function getTemplates($ids = null)
    {
        if (empty($ids)) {
            $templates = $this->order('sort, id DESC')->fetchAll('id');
        } else {
            $ids = waUtils::toIntArray($ids);
            $templates = $this->where('id', $ids)->order('sort, id DESC')->fetchAll('id');
        }

        return $this->contentDecode($templates);
    }

    private function contentEncode($templates = [])
    {
        static $fields;
        if (!$fields) {
            $fields = $this->getMetadata();
            $fields = array_keys($fields);
        }
        foreach ($templates as &$template) {
            $content_key = array_diff(array_keys($template), $fields);
            $ext_content = array_intersect_key($template, array_flip($content_key));
            if (isset($template['content']) || $ext_content) {
                $template['content'] = array_merge(ifempty($template, 'content', []), (array) $ext_content);
                $template['content'] = json_encode($template['content']);
            }
        }
        unset($template);

        return $templates;
    }

    private function contentDecode($templates = [])
    {
        foreach ($templates as &$template) {
            if (!empty($template['content'])) {
                $template['content'] = json_decode($template['content'], true);
            }
        }
        unset($template);

        return $templates;
    }

    public function getByField($field, $value = null, $all = false, $limit = false)
    {
        $dbq = $this->select('*')->where($this->getWhereByField($field, $value))->order('sort, id DESC');
        if (is_array($field)) {
            $all = $value;
        }

        if ($all) {
            return $this->contentDecode($dbq->fetchAll());
        }
        $result = $this->contentDecode([$dbq->fetchAssoc()]);

        return reset($result);
    }

    public function updateById($id, $data, $options = null, $return_object = false)
    {
        $data = $this->contentEncode([$data]);
        $data = reset($data);

        return parent::updateById($id, $data, $options, $return_object);
    }

    public function updateByField($field, $value, $data = null, $options = null, $return_object = false)
    {
        return parent::updateByField($field, $value, $data, $options, $return_object);
    }

    public function insert($data, $type = 0)
    {
        $data = $this->contentEncode([$data]);

        return parent::insert(reset($data), $type);
    }

    public function multipleInsert($data)
    {
        $data = $this->contentEncode($data);

        return parent::multipleInsert($data);
    }
}
