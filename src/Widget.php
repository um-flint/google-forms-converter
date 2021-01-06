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
     * @var array|null
     */
    public ?array $legend;

    /**
     * @var array|null
     */
    public ?array $columns;

    /**
     * @var string|null
     */
    public ?string $name;

    /**
     * Widget constructor.
     * @param string $id
     * @param bool $required
     * @param array|null $options
     * @param array|null $legend
     * @param array|null $columns
     * @param string|null $name
     */
    public function __construct(string $id, bool $required, ?array $options = null, ?array $legend = null, ?array $columns = null, ?string $name = null)
    {
        $this->id = $id;
        $this->required = $required;
        $this->options = $options;
        $this->legend = $legend;
        $this->columns = $columns;
        $this->name = $name;
    }
}