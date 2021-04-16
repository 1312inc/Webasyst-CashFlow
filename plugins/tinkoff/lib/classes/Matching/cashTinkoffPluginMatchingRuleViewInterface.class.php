<?php

interface cashTinkoffPluginMatchingRuleViewInterface
{
    public function getHtml(array $params = []): string;

    public function getForm(array $params = []): string;
}
