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

    public function __construct(string $id, bool $required)
    {
        $this->id = $id;
        $this->required = $required;
    }
}