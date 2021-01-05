<?php

namespace UMFlint\GoogleFormsConverter;

use DOMDocument;
use DOMXPath;
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
    public string $fbzx;

    /**
     * @var string
     */
    public string $title;

    /**
     * @var string
     */
    public string $path;

    /**
     * @var string
     */
    public string $action;

    /**
     * @var string
     */
    public string $description;

    /**
     * @var string
     */
    public string $header;

    /**
     * @var int
     */
    public int $sectionCount = 1;

    /**
     * @var array
     */
    public array $fields = [];

    public static array $fieldTypes = [

    ];

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

        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML((string)$response->getBody());

        return $dom;
    }

    /**
     * @param DOMDocument $form
     * @return $this
     * @throws \Exception
     */
    protected function parseForm(DOMDocument $form): self
    {
        $scripts = $form->getElementsByTagName('script');
        foreach ($scripts as $script) {
            if (strpos($script->textContent, 'var FB_PUBLIC_LOAD_DATA_') !== false) {
                $text = str_replace(['var FB_PUBLIC_LOAD_DATA_ =', ';'], '', $script->textContent);

                $this->form = json_decode($text, 0);
                break;
            }
        }

        return $this;
    }

    /**
     * @param DOMDocument $form
     * @return $this
     * @throws \Exception
     */
    protected function parseFbzx(DOMDocument $form): self
    {
        $xp = new DOMXpath($form);
        $nodes = $xp->query('//input[@name="fbzx"]');
        if (!$nodes) {
            throw new \Exception('Invalid form! Could not find the fbzx field.');
        }

        $node = $nodes->item(0);
        $this->fbzx = $node->getAttribute('value');

        return $this;
    }

    public function build(): string
    {
        $rawForm = $this->getForm();
        $this->parseForm($rawForm);
        $this->parseFbzx($rawForm);

        $this->title = $this->form[3];
        $this->path = $this->form[2];
        $this->action = $this->form[14];
        $this->description = $this->form[1][0];
        $this->header = $this->form[1][8];
        //$this->sectionCount = 1;
        // Loop through fields here to get better idea of sectionCount.

        print_r($this->form);
        exit;

        $form = '';

        return $form;
    }
}