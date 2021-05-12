<?php

namespace Juancrrn\Lyra\Domain;

/**
 * Clase genérica para contener una representación humanamente legible,
 * generalmente con un valor, un título y una descripción.
 * 
 * @package lyra
 *
 * @author juancrrn
 *
 * @version 0.0.1
 */

class GenericHumanModel
{

    /**
     * @var mixed $value
     */
    private $value;

    /**
     * @var string $title
     */
    private $title;

    /**
     * @var string $description
     */
    private $description;
    
    /**
     * Constructor
     * 
     * @param mixed $value
     * @param string $title
     * @param string $description
     */
    public function __construct(
        mixed $value,
        string $title,
        string $description
    )
    {
        $this->value = $value;
        $this->title = $title;
        $this->description = $description;
    }

    /*
     *
     * Getters
     * 
     */

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}