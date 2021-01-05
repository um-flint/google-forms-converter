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
     * @var string|null
     */
    public ?string $header;

    /**
     * @var int
     */
    public int $sectionCount = 1;

    /**
     * @var array
     */
    public array $fields = [];

    /**
     * @var array|int[]
     */
    public static array $fieldTypes = [
        'FieldShort'      => 0,
        'FieldParagraph'  => 1,
        'FieldChoices'    => 2,
        'FieldDropdown'   => 3,
        'FieldCheckboxes' => 4,
        'FieldLinear'     => 5,
        'FieldTitle'      => 6,
        'FieldGrid'       => 7,
        'FieldSection'    => 8,
        'FieldDate'       => 9,
        'FieldTime'       => 10,
        'FieldImage'      => 11,
        'FieldVideo'      => 12,
        'FieldUpload'     => 13,
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

    /**
     * @return $this
     * @throws \Exception
     */
    protected function parseFields(): self
    {
        foreach ($this->form[1][1] as $data) {
            $field = new Field($data[0], $data[1], $data[2], $data[3]);

            switch ($field->typeId) {
                case self::$fieldTypes['FieldShort']:
                case self::$fieldTypes['FieldParagraph']:
                    $widget = new Widget($data[4][0][0], $data[4][0][2]);
                    $field->setWidgets([$widget]);
            }

            $this->fields[] = $field;
        }

        return $this;
    }

    public function build(): string
    {
        $rawForm = $this->getForm();
        $this->parseForm($rawForm);
        $this->parseFbzx($rawForm);
        $this->parseFields();

        $this->title = $this->form[3];
        $this->path = $this->form[2];
        $this->action = $this->form[14];
        $this->description = $this->form[1][0];
        $this->header = $this->form[1][8];
        //$this->sectionCount = 1;
        // Loop through fields here to get better idea of sectionCount.

        echo "<pre>";
        print_r($this->fields);
        exit;

        $form = '';

        return $form;
    }
}