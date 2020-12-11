<?php

namespace UMFlint\GoogleFormsConverter;

class Form
{
    /**
     * @var string
     */
    protected $url;

    public function __construct(string $url)
    {
        $this->setUrl($url);
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl(string $url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }
}