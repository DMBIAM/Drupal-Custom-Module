<?php

declare(strict_types=1);

namespace Drupal\encryption\Services\v2_0\Encrypt;

use Aws\Kms\KmsClient;

/**
 * @author bitsJuan.Diaz
 *
 * Model represent Key Management Service as Kms.
 */
class Kms
{

  public function __construct(
    protected readonly string    $arn,
    protected readonly string    $encryptionAlgorithm,
    protected readonly bool      $testMode,
    protected readonly KmsClient $kmsClient,
  ) {
  }

  public function arn(): string
  {
    return $this->arn;
  }

  public function encryptionAlgorithm(): string
  {
    return $this->encryptionAlgorithm;
  }


  public function isTestMode(): bool
  {
    return $this->testMode;
  }


  public function kmsClient(): KmsClient
  {
    return $this->kmsClient;
  }


}
