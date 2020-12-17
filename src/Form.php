<?php

namespace UMFlint\GoogleFormsConverter;

use GuzzleHttp\Client;

class Form
{
    /**
     * @var string
     */
    protected string $url;

    /**
     * @var array
     */
    protected array $form;

    /**
     * @var string
     */
    protected string $fbzx;

    /**
     * Form constructor.
     * @param string $url
     */
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

    /**
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function getForm()
    {
        $client = new Client();
        $response = $client->get($this->getUrl());

        return (string)$response->getBody();
    }

    protected function parseForm(string $form): self
    {


        return $this;
    }

    /**
     * @param string $form
     * @return $this
     * @throws \Exception
     */
    protected function parseFbzx(string $form): self
    {
        $matches = [];
        preg_match('/name="fbzx" value="(.*)">/', $form, $matches);

        if (empty($matches)) {
            throw new \Exception('Invalid form! Could not find the fbzx field.');
        }

        print_r($matches[1]);
        exit;

        $this->fbzx = $matches[1];

        return $this;
    }

    public function build(): string
    {
        $rawForm = $this->getForm();
        $this->parseForm($rawForm);
        $this->parseFbzx($rawForm);


        $form = '';

        return $form;
    }
}