<?php

namespace UMFlint\GoogleFormsConverter;

class Widget
{
    /**
     * @var string
     */
    public string $id;

    /**
     * @var bool
     */
    public bool $required;

    /**
     * @var array|null
     */
    public ?array $options;

    /**
     * Widget constructor.
     * @param string $id
     * @param bool $required
     * @param array|null $options
     */
    public function __construct(string $id, bool $required, ?array $options = null)
    {
        $this->id = $id;
        $this->required = $required;
        $this->options = $options;
    }
}