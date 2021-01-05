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
     * @var int
     */
    public int $typeId;

    /**
     * @var array
     */
    public array $widgets;

    /**
     * Field constructor.
     * @param int $id
     * @param string $label
     * @param string|null $description
     * @param int $typeId
     */
    public function __construct(int $id, string $label, ?string $description, int $typeId)
    {
        $this->id = $id;
        $this->label = $label;
        $this->description = $description;
        $this->typeId = $typeId;
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