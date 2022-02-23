<?php

class cashCharttogglePlugin extends waPlugin
{
    public function handleBackendLayoutHook(): array
    {
        $jsSrc = wa()->getAppStaticUrl() . $this->getUrl('js/charttoggle.js', true);

        $js = <<<HTML
<script src="{$jsSrc}"></script>
HTML;

        return ['js' => $js];
    }
}
