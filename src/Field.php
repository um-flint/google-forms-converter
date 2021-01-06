<?php

namespace UMFlint\GoogleFormsConverter;

class Field
{
    /**
     * @var int
     */
    public int $id;

    /**
     * @var string
     */
    public string $label;

    /**
     * @var string|null
     */
    public ?string $description;

    /**
     * @var string
     */
    public string $type;

    /**
     * @var array
     */
    public array $widgets;

    /**
     * Field constructor.
     * @param int $id
     * @param string $label
     * @param string|null $description
     * @param string $type
     */
    public function __construct(int $id, string $label, ?string $description, string $type)
    {
        $this->id = $id;
        $this->label = $label;
        $this->description = $description;
        $this->type = $type;
    }

    /**
     * @param array $widgets
     * @return $this
     * @throws \Exception
     */
    public function setWidgets(array $widgets): self
    {
        foreach ($widgets as $widget) {
            if (!$widget instanceof Widget) {
                throw new \Exception('Invalid widget assignment.');
            }

            $this->widgets[] = $widget;
        }

        return $this;
    }
}