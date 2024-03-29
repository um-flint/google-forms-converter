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
     * @var string|null
     */
    public ?string $description;

    /**
     * @var string|null
     */
    public ?string $header;

    /**
     * @var bool
     */
    public bool $askEmail = false;

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
        'short'      => 0,
        'paragraph'  => 1,
        'choices'    => 2,
        'dropdown'   => 3,
        'checkboxes' => 4,
        'linear'     => 5,
        'title'      => 6,
        'grid'       => 7,
        'section'    => 8,
        'date'       => 9,
        'time'       => 10,
        'image'      => 11,
        'video'      => 12,
        'upload'     => 13,
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
        if ($nodes->length === 0) {
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
            $field = new Field($data[0], $data[1], $data[2], array_search($data[3], self::$fieldTypes));

            switch ($data[3]) {
                case self::$fieldTypes['short']:
                case self::$fieldTypes['paragraph']:
                    $widget = new Widget($data[4][0][0], $data[4][0][2]);
                    $field->setWidgets([$widget]);
                    break;
                case self::$fieldTypes['choices']:
                case self::$fieldTypes['checkboxes']:
                case self::$fieldTypes['dropdown']:
                    $options = [];
                    foreach ($data[4][0][1] as $optionData) {
                        $optionInfo = [
                            'label' => $optionData[0]
                        ];

                        // Handle the case for missing information in option object
                        if (count($optionData) > 2) {
                            $optionInfo['href'] = $optionData[2];
                        }

                        if (count($optionData) > 4) {
                            $optionInfo['custom'] = $optionData[4];
                        }

                        $options[] = $optionInfo;
                    }

                    $widget = new Widget($data[4][0][0], $data[4][0][2], $options);
                    $field->setWidgets([$widget]);
                    break;
                case self::$fieldTypes['linear']:
                    $options = [];
                    foreach ($data[4][0][1] as $optionData) {
                        $options[] = [
                            'label' => $optionData[0],
                        ];
                    }

                    $widget = new Widget($data[4][0][0], $data[4][0][2], $options, [
                        'first' => $data[4][0][3][0],
                        'last'  => $data[4][0][3][1],
                    ]);
                    $field->setWidgets([$widget]);
                    break;
                case self::$fieldTypes['grid']:
                    $widgets = [];
                    foreach ($data[4] as $widgetItem) {
                        $columns = [];
                        foreach ($widgetItem[1] as $columnItem) {
                            $columns[] = [
                                'label' => $columnItem[0]
                            ];
                        }
                        $widgets[] = new Widget($data[4][0][0], $data[4][0][2], null, null, $columns, $widgetItem[3][0]);
                    }
                    $field->setWidgets($widgets);
                    break;
                case self::$fieldTypes['date']:
                    $widget = new Widget($data[4][0][0], $data[4][0][2], [
                        'time' => $data[4][0][7][0],
                        'year' => $data[4][0][7][1],
                    ]);
                    $field->setWidgets([$widget]);
                    break;
                case self::$fieldTypes['time']:
                    $widget = new Widget($data[4][0][0], $data[4][0][2], [
                        'duration' => $data[4][0][6][0],
                    ]);
                    $field->setWidgets([$widget]);
                    break;
                case self::$fieldTypes['video']:
                    $widget = new Widget($data[6][0], false, [
                        'w'        => $data[4][0][6][2][0],
                        'h'        => $data[4][0][6][2][1],
                        'showText' => $data[4][0][6][2][2],
                    ]);
                    $field->setWidgets([$widget]);
                    break;
                case self::$fieldTypes['image']:
                    $widget = new Widget($data[6][0], false, [
                        'w'        => $data[4][0][6][2][0],
                        'h'        => $data[4][0][6][2][1],
                        'showText' => $field->description !== '',
                    ]);
                    $field->setWidgets([$widget]);
                    break;
                case self::$fieldTypes['upload']:
                    $widget = new Widget($data[4][0][0], $data[4][0][2], [
                        'types'          => $data[4][0][10][1],
                        'maxUploads'     => $data[4][0][10][2],
                        'maxSizeInBytes' => $data[4][0][10][3],
                    ]);
                    $field->setWidgets([$widget]);
                    break;
                case self::$fieldTypes['section']:
                    $this->sectionCount++;
                    break;
            }

            $this->fields[] = $field;
        }

        return $this;
    }

    /**
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function build(): array
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

        if ($this->form[1][10] && count($this->form[1][10]) >= 4) {
            $this->askEmail = $this->form[1][10][4] === 1;
        }

        return [
            'path'         => $this->path,
            'action'       => $this->action,
            'fbzx'         => $this->fbzx,
            'title'        => $this->title,
            'description'  => $this->description,
            'header'       => $this->header,
            'sectionCount' => $this->sectionCount,
            'askEmail'     => $this->askEmail,
            'fields'       => $this->fields,
        ];
    }
}