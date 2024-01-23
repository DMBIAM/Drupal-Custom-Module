<?php

declare(strict_types=1);

namespace Drupal\encryption\Services\v2_0\Encrypt\Exceptions;

use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;

/**
 * @author bitsJuan.Diaz
 */
class ErrorCreateEncryptClientException extends \RuntimeException
{

  public static function keyNotFound(string $key, string $name): static
  {
    return new static(sprintf('key machine not found name: <%s>', $name));
  }

  public static function valueNotFound(string $message): static
  {
    return new static($message);
  }
}
