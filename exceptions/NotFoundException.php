<?php

namespace alfianchii\phpmvc\exceptions;

class NotFoundException extends \Exception
{
    // Override the $message and $code
    protected $message = "Page not found.";
    protected $code = 404;
}
