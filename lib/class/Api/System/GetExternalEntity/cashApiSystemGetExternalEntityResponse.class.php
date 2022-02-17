<?php

final class cashApiSystemGetExternalEntityResponse extends cashApiAbstractResponse
{
    public function __construct(cashExternalInfoDto $externalInfoDto)
    {
        parent::__construct();

        $this->response = [
            'app' => $externalInfoDto->getApp(),
            'app_name' => $externalInfoDto->getAppName(),
            'app_icon_url' => $externalInfoDto->getAppIconUrl(),
            'entity_id' => $externalInfoDto->getId(),
            'entity_name' => $externalInfoDto->getTitle(),
            'entity_url' => $externalInfoDto->getUrl(),
            'entity_icon' => $externalInfoDto->getIconUrl(),
            'entity_data' => $externalInfoDto->getData(),
        ];
    }
}
