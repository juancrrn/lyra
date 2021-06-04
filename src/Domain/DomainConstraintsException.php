<?php

namespace Juancrrn\Lyra\Domain;

use Exception;
use Throwable;

class DomainConstraintsException extends Exception
{

    private $constraints;
    
    public function __construct(array $constraints, string $message, $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->constraints = $constraints;
    }

    public function __toString()
    {
        $return = __CLASS__ . ": [{$this->code}]: {$this->message}\n";

        $return .= "Found constraints:\n";

        foreach ($this->constraints as $constraint) {
            $return .= "- " . $constraint;
        }
        
        return $return;
    }
}