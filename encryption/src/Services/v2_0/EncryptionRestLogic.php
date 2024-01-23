<?php

namespace Drupal\encryption\Services\v2_0;

use Drupal\encryption\Services\v2_0\Encrypt\Encrypt;

/**
 * @author bitsJuan.Diaz
 */
class EncryptionRestLogic
{
  public function __construct(protected readonly Encrypt $encrypt)
  {
  }

  public function getPublicKey(): array
  {
    return $this->encrypt->getPublicKey();
  }

  public function encrypt(string $plainText): array
  {
    return $this->encrypt->doEncrypt($plainText);
  }

  public function decrypt(string $plainText): array
  {
    return $this->encrypt->doDecrypt($plainText);
  }

}
