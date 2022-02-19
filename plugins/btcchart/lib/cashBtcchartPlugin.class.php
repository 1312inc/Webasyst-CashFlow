<?php

class cashBtcchartPlugin extends waPlugin
{
    public function handleBackendLayoutHook(): array
    {
        $jsSrc = wa()->getAppStaticUrl() . $this->getUrl('js/btcchart.js', true);

        $js = <<<HTML
<script src="{$jsSrc}"></script>
<script src="https://cdn.jsdelivr.net/npm/@coinpaprika/widget-currency/dist/widget.min.js"></script>    
HTML;

        return ['js' => $js];
    }
}
