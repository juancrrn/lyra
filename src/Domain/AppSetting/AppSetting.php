<?php

namespace Juancrrn\Lyra\Domain\AppSetting;

class AppSetting
{

    /**
     * @var int Identifier
     */
    private $id;

    /**
     * @var string Short name
     */
    private $shortName;

    /**
     * @var string Full name
     */
    private $fullName;

    /**
     * @var string Description
     */
    private $description;

    /**
     * @var string Value
     */
    private $value;

    public function __construct(
        ?int    $id,
        string  $shortName,
        string  $fullName,
        string  $description,
        string  $value
    )
    {
        $this->id           = $id;
        $this->shortName    = $shortName;
        $this->fullName     = $fullName;
        $this->description  = $description;
        $this->value        = $value;
    }

    public static function constructFromMysqliObject(object $mysqli_object): self
    {
        return new self(
            $mysqli_object->id,
            $mysqli_object->short_name,
            $mysqli_object->full_name,
            $mysqli_object->description,
            $mysqli_object->value
        );
    }

    /*
     * 
     * Getters
     * 
     */

    public function getId(): int
    {
        return $this->id;
    }

    public function getShortName(): string
    {
        return $this->shortName;
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}