<?php

namespace Findable\Exceptions;

use RuntimeException;

/**
 * Class FindableException
 *
 * Base exception for all Findable-related errors.
 * Use this to signal configuration, model, or runtime issues specific to the Findable package.
 *
 * @package Findable\Exceptions
 */
class FindableException extends RuntimeException
{
    // You can extend this later with specific static helpers like:
    // public static function missingCertificate(string $path): self { ... }
}
