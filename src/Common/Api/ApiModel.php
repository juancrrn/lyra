<?php

namespace Juancrrn\Lyra\Common\Api;

abstract class ApiModel
{

    abstract public function consume(?object $requestContent): void;
}