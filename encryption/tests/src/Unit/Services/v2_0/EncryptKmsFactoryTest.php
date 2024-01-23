<?php

namespace Drupal\Tests\encryption\Unit\Services\v2_0;

use Aws\Credentials\Credentials;
use Aws\Kms\KmsClient;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\encryption\Services\v2_0\Encrypt\EncryptKmsFactory;
use Drupal\encryption\Services\v2_0\Encrypt\Exceptions\ErrorCreateEncryptClientException;
use Drupal\encryption\Services\v2_0\Encrypt\Kms;
use Drupal\payment\Services\PaymentUtilsServices;
use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class EncryptKmsFactoryTest extends EncryptModuleUnitTestCase
{

  private EncryptKmsFactory $encryptKmsFactory;
  private PaymentUtilsServices|MockObject $utilsServices;
  private ConfigFactory|MockObject $configFactory;
  private ImmutableConfig|MockObject $config;

  protected function setUp()
  {
    parent::setUp();
    \Drupal::unsetContainer();
    $container = new ContainerBuilder();

    $this->configFactory = $this->getMockBuilder(ConfigFactory::class)
      ->disableOriginalConstructor()
      ->setMethods(['get'])
      ->getMock();

    $storage = $this
      ->getMockBuilder(StorageInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $event_dispatcher = $this
      ->getMockBuilder(EventDispatcherInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $typed_config = $this
      ->getMockBuilder(TypedConfigManagerInterface::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->config = new ImmutableConfig('tbo_encrypt.settings', $storage, $event_dispatcher, $typed_config);
    $this->config->setData($this->getConfigMock());

    $this->configFactory->expects($this->any())
      ->method('get')
      ->will(
        $this->returnValue($this->config),
      );

    $this->utilsServices = $this->createMock(PaymentUtilsServices::class);

    $container->set('config.factory', $this->configFactory);

    $this->encryptKmsFactory = new EncryptKmsFactory(
      $this->configFactory,
      $this->utilsServices
    );

    \Drupal::setContainer($container);

  }

  public function testCreateKmsExceptionEmptyConfig()
  {
    $this->utilsServices->expects($this->any())
      ->method('getKeyMachine')
      ->willReturn('xxxx');

    $this->config->setData([]);

    self::expectException(ErrorCreateEncryptClientException::class);
    self::expectExceptionMessage('data config not found!');

    $this->encryptKmsFactory->__invoke();
  }

  public function testCreateKmsWithValidConfig()
  {
    $this->utilsServices->expects($this->any())
      ->method('getKeyMachine')
      ->willReturn('pruebita');

    $kms = $this->encryptKmsFactory->__invoke();

    self::assertNotNull($kms);
    self::assertInstanceOf(Kms::class, $kms);
    self::assertEquals(self::mockKms(), $kms);
  }

  public function testWhenKeyMachineArnNotFindException()
  {
    $config_data = [
      "arn" => ""
    ];

    $this->config->setData($config_data);


    self::expectException(ErrorCreateEncryptClientException::class);
    self::expectExceptionMessage('key machine not found name: <ARN>');

    $this->encryptKmsFactory->__invoke();

  }



}
