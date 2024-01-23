<?php

namespace Drupal\Tests\encryption\Unit\Services\v2_0;

use Aws\Credentials\Credentials;
use Aws\Kms\KmsClient;
use Aws\Result;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\encryption\Services\v2_0\Encrypt\Encrypt;
use Drupal\encryption\Services\v2_0\Encrypt\EncryptKmsFactory;
use Drupal\encryption\Services\v2_0\Encrypt\Exceptions\ErrorCreateEncryptClientException;
use Drupal\encryption\Services\v2_0\Encrypt\Kms;
use Drupal\payment\Services\PaymentUtilsServices;
use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class EncryptTest extends EncryptModuleUnitTestCase
{

  private EncryptKmsFactory|MockObject $clientFactory;

  private KmsClient|MockObject $kmsClient;

  private Encrypt $encrypt;

  protected function setUp()
  {
    parent::setUp();

    \Drupal::unsetContainer();
    $container = new ContainerBuilder();

    $config_factory = $this->getMockBuilder(ConfigFactory::class)
      ->disableOriginalConstructor()
      ->getMock();

    $utils = $this->createMock(PaymentUtilsServices::class);

    $config_obj = $this->createMock(ImmutableConfig::class);
    $config_obj->setData($this->getConfigMock());

    $config_factory->expects($this->any())
      ->method('get')
      ->will(
        $this->returnValue($config_obj),
      );

    $this->clientFactory = $this->getMockBuilder(EncryptKmsFactory::class)
      ->setConstructorArgs([
        $config_factory,
        $utils
      ])
      ->getMock();

    $this->kmsClient = $this->getMockBuilder(KmsClient::class)
      ->disableOriginalConstructor()
      ->getMock();


    $container->set('encryption.v2_0.encrypt_factory', $this->clientFactory);

    $this->encrypt = new Encrypt($this->clientFactory);

    \Drupal::setContainer($container);

  }


  public function testGetPublicKeySuccess200()
  {
    $result = $this->createMock(Result::class);

    $result->expects($this->any())
      ->method('get')
      ->willReturn(<<<EOT
      '0é\x02"0\x06\t*åHå¸\x01\x01\x01\x05\x00\x03é\x02\x0F\x000é\x02\x02é\x02\x01\x00Â╔w¢ç\x15▀½\x06E═%öq* )\
      x0E\x17╗d░414╗?\fB└F·\x18Hrþm\t&¨\x1C\÷┼wK»Ô╠▒*]*¬+¥\x1F!}├\x01Z+I rÈl[ó1│\x19\x08\tƒ5Ñ└'X\x06\x14\tôè╝)zU╚ÅÏBgH/Ã
      a\x7FÚ|╔Mö╩t/ã▒gñ\x15\x02kEF/Ûøò¥§(µÅ+¾Þ§Ø\nÍY~)x\x08¾\x16╬3-×r8vA├w‗┘ËC:4Í*Ë
      █;}a\tÝÊV├<╔╔­╔\f▒¿·qq4é┌^%N<ÍÐ\x15┬4n░▓§'‗\x033Ò¾çÒÖâZÔ]¡á\x03.ÌâÜþÑî■╣ÎìÑxc¨%ó╚╦^I`¶³êE\x14┌A°ï©"OQk{}°\x01▓╦┴mËo_┼\x01b}Ó┌äµçVæÎB└¿┼à£d\x1F÷\x15´\x1AFÆ×º2Þ■Åõ»\x19_{┐/º\x08▄«▀¾;¶─ï¢Kº1éý╔\x08\x19MÈ─\t─£pe¢\x02\x14îý╔uYäíù═äUC+ lÐ┼Æ,ò\x06┘qâÒé\x05;ð±ç±\e}R╚Ð¤ª¿ubÚe3çï\v1\x1CÕ«\x1CÇ▄┼Iù¹┴ÁE\x00}§ìñ\x17ÊÓ®&§'║Æ`ÐTï┤ÝƒQ;\x1E\x12ÄJ@xÀiïªÆXÍåÕTì ╩Ý\x10┴\x02╝5^V\x00Ãñs©║L°]j\x1D\tä┬ı┴\x00╗q§ê┌║:æûv\x031"Ó;
      òb\x7F±Èyý#ù\x14╬Û²▒+['Ð▓▄gb;F▓Pç\x1FG═\x02\x03\x01\x00\x01'
EOT
      );

    $this->kmsClient->expects($this->any())
      ->method('__call')
      ->willReturn(
        $this->returnValue(
          $result
        )
      );

    $this->clientFactory->expects($this->any())
      ->method('__invoke')
      ->will(
        $this->returnValue(new Kms(
          'pruebita',
          'RSAES_OAEP_SHA_256', TRUE, $this->kmsClient))
      );

    $response = $this->encrypt->getPublicKey();

    $expected = [
      "testMode" => true,
      "status" => 200,
      "format" => "base64",
      "key" => $this->getStringPublicKey(),
    ];

    self::assertNotNull($response);
    self::assertIsArray($response);
    self::assertEquals($expected, $response);
  }

  public function testGetPublicKeyWithError500()
  {
    $this->clientFactory->expects($this->any())
      ->method('__invoke')
      ->willThrowException(ErrorCreateEncryptClientException::valueNotFound('data config not found!'));

    $response = $this->encrypt->getPublicKey();

    $expected = [
      "status" => 500,
      "error" => "Se presentó un error interno, por favor intente más tarde",
    ];

    self::assertNotNull($response);
    self::assertIsArray($response);
    self::assertEquals($expected, $response);
  }

}
