<?php

namespace Drupal\Tests\encryption\Unit\Services\v2_0;

use Aws\Kms\KmsClient;
use Drupal\encryption\Services\v2_0\Encrypt\Kms;
use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;

class KmsTest extends EncryptModuleUnitTestCase
{
  private KmsClient|MockObject $kmsClient;

  protected function setUp()
  {
    parent::setUp();
    $this->kmsClient = $this->createMock(KmsClient::class);
  }

  public function testCanGetAndSetData(): void
  {
    $kms = new Kms(
      'pruebita',
        'RSAES_OAEP_SHA_256',
      TRUE,
      $this->kmsClient
    );

    self::assertSame('pruebita', $kms->arn());
    self::assertSame('RSAES_OAEP_SHA_256', $kms->encryptionAlgorithm());
    self::assertSame(TRUE, $kms->isTestMode());
    self::assertSame($this->kmsClient, $kms->kmsClient());

  }

  /**
   * @dataProvider healthStatusProvider
   */
  public function testIsTestMode(bool $value, bool $expected): void
  {
    $kms = new Kms(
      'pruebita',
      'RSAES_OAEP_SHA_256',
      $value,
      $this->kmsClient
    );

    self::assertSame($expected, $kms->isTestMode());
  }


  public function healthStatusProvider(): \Generator
  {
    yield 'Is true mode' => [TRUE, TRUE];
    yield 'Is false mode' => [FALSE, FALSE];
  }

}
