<?php

class cashExternalInfoDto
{
    /**
     * @var string
     */
    private $app;

    /**
     * @var string
     */
    private $appName;

    /**
     * @var string
     */
    private $appIconUrl;

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $iconUrl;

    /**
     * @var string
     */
    private $url;

    /**
     * @var array|null
     */
    private $data;

    public function __construct(
        string $app,
        string $appName,
        string $appIconUrl,
        int $id,
        string $title,
        string $iconUrl,
        string $url,
        ?array $data
    ) {
        $this->app = $app;
        $this->appIconUrl = $appIconUrl;
        $this->id = $id;
        $this->title = $title;
        $this->iconUrl = $iconUrl;
        $this->url = $url;
        $this->data = $data;
        $this->appName = $appName;
    }

    public function getAppName(): string
    {
        return $this->appName;
    }

    public function getApp(): string
    {
        return $this->app;
    }

    public function getAppIconUrl(): string
    {
        return $this->appIconUrl;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getIconUrl(): string
    {
        return $this->iconUrl;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getData(): ?array
    {
        return $this->data;
    }
}
