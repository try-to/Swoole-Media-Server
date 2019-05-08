<?php

namespace TrytoMediaServer\Exceptions;

class BaseException extends \Exception
{
    public function errorMessage()
    {
        return sprintf('%s (%s:%s)', trim($this->getMessage()), $this->getFile(), $this->getLine());
    }
}
