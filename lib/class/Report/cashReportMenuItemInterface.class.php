<?php

interface cashReportMenuItemInterface
{
    public function getIdentifier(): string;
    public function getIcon(): string;
    public function getName(): string;
}
