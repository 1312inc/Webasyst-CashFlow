<?php

class cashCharttogglePlugin extends waPlugin
{
    public function handleBackendLayoutHook(): array
    {
        $jsSrc = wa()->getAppStaticUrl() . $this->getUrl('js/chartTogglePlugin.js', true);

        $js = <<<HTML
<script src="{$jsSrc}"></script>
HTML;

        return ['js' => $js];
    }
}
