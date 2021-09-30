<?php

namespace App\Exceptions;

use App\Models\UrlEntry;
use JetBrains\PhpStorm\Pure;
use Throwable;

class UrlExistsException extends \Exception
{
    private UrlEntry $urlEntry;

    #[Pure]
    public function __construct(UrlEntry $urlEntry, $message = 'This URL is already registered.', $code = 0, Throwable $previous = null)
    {
        $this->urlEntry = $urlEntry;

        parent::__construct($message, $code, $previous);
    }

    public function getUrlEntry(): UrlEntry
    {
        return $this->urlEntry;
    }
}
