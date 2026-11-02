<?php

/**
 * Class cashApiTemplateGetListHandler
 */
class cashApiTemplateGetListHandler implements cashApiHandlerInterface
{
    /**
     * @param $request
     * @return array
     * @throws waException
     */
    public function handle($request): array
    {
        if (!cashHelper::isPremium()) {
            return [];
        }
        $model = cash()->getModel('cashTemplate');
        $templates = $model->getAllForMe();
        foreach ($templates as &$_template) {
            $contents = [];
            foreach ((array) $_template['content'] as $_name => $_value) {
                $contents[] = [
                    'name' => (string) $_name,
                    'value' => (string) $_value,
                ];
            }
            $_template['content'] = $contents;
        }
        unset($_template);

        return $templates;
    }
}
