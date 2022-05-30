<?php

final class cashCsvImportMenuItem implements cashImportMenuItemInterface
{
    public function getIdentifier(): string
    {
        return cashCsvImportHandler::IDENTIFIER;
    }

    public function getAnchor(): string
    {
        return '<i class="fas fa-file-excel" style="color: #499b5e;"></i> ' . _w('Excel (CSV)');
    }

    public function getUrl(): ?string
    {
        return null;
    }
}
