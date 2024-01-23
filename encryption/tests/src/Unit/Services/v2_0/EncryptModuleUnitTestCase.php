<?php

namespace Drupal\Tests\encryption\Unit\Services\v2_0;

use Aws\Credentials\Credentials;
use Aws\Kms\KmsClient;
use Drupal\encryption\Services\v2_0\Encrypt\Kms;
use Drupal\Tests\UnitTestCase;

class EncryptModuleUnitTestCase extends UnitTestCase
{

  protected function getConfigMock(): array
  {
    $private_key = '-----BEGIN RSA PRIVATE KEY-----
MIICXAIBAAKBgQC7xzfMsVgFc/q1wUQ7vAUU7CD8W5eq0PnJb0Hv7Py1GQ+qYaqi
asOqOr6T2FizbzSbXd+ZirEiZVdtFX9nKK4OlVHRzDSGulioUESLUuNQau0BeWFs
EwxCMIMkAMM1fI5zDMp1PvxULdC5hFTLDXNCf5DYYl4Xkc1LNsa4XYQV1QIDAQAB
AoGARRGwGqCYydixPS2LlZVBIUMBlxFxpikb19YOoNvA0DQZqQgnpXoz4medNfB8
H/Qlm4hZ+LYlFYvFLqCbriwuaRl3utzULP6XxVjI8NlLbbg+sXquDAJVtiIFVpBs
VNbvBFFMG9kwM0UnfRTcLDVu5kPH8PSpkuEF6BKRS2oyXcECQQDgteyUuDvMejIR
sYHf+GDOhtY6Ncy25cEgk07xSNz84uRhMBe2lVI9rTEmE2lSVSBBfsdKwums2VOK
bj8uJQYJAkEA1ezLEKCdOWN8VZLe8jQIGoPX7kYqIo1BiaUa+8eER/tMZlCsXDPQ
wRBfRBiiDGO9KAWR8i0vRMGTYAnol31kbQJAN1DxdUbJCbQHAU4GH6FgC1csA1Zd
F6UFXsSEiWcbZ3FfMQGKxNqLTT2GPM5IfgkQkK7p1mCW74LsSsaK7QwWKQJAHJ+n
eB0VjHU8ULLrM9s0bl/Px6kJwD/IUiOOXbwPfhYo3dPTjC6+suZ+6LynCiNaTv2X
zqCvH3MLRiFtRr/XbQJBANDOugkjgfTQKt2yHWEPMp+pNeRyPIycuHQq+ejoTp+G
y0SXaEGYTNdLpZ4D1mCVea/4qnhlnW8ir7KEC6ecI0I=
-----END RSA PRIVATE KEY-----';
    return [
      "secretNamePublic" => "public",
      "secretNamePrivate" => "private",
      "region" => "us-east-1",
      "test_mode" => true,
      "password" => "000",
      "authenticationIam" => 0,
      "accessKey" => "kms_aws_accesskey",
      "secretKey" => "kms_aws_secretkey",
      "arn" => "kms_aws_arn",
      "encryptionAlgorithm" => "RSAES_OAEP_SHA_256",
      "profile" => "default",
      "version" => "2014-11-01",
      'private_key' => $private_key,
    ];
  }

  protected static function mockKms(): Kms
  {
    return new Kms(
      'pruebita',
      'RSAES_OAEP_SHA_256',
      TRUE,
      self::createKmsClient(),
    );

  }

  protected static function createKmsClient(): KmsClient
  {
   return new KmsClient([
      "version" => "2014-11-01",
      "region" => "us-east-1",
      "credentials" => self::createCredentials()]);
  }

  protected static function createCredentials(): Credentials
  {
    return new Credentials('pruebita', 'pruebita');
  }

  protected function getStringPublicKey(): string
  {
    $string = 'epaLS0tLS1CRUdJTiBQVUJMSUMgS0VZLS0tLS0KSUNBZ0lDQWdKekREcVFJaU1BWUpLc09sU01PbHdyZ0JBUUVGQUFQRHFRSVBBREREcVFJQ3c2a0NBUUREZ3VLVmxIZkNvc09uRmVLV2dNSzlCa1hpbFpBbHc3WnhLaUFwWEFvZ0lDQWdJQ0I0
TUVVWDRwV1haT0tXa1RReE5PS1ZsejhNUXVLVWxFYkN0eGhJY3NPK2JRa213cWdjWE1PMzRwUzhkMHZDdThPVTRwV2c0cGFTS2wwcXdxd3J3cVVmSVgzaWxKd0JXaXRKSUhMRGlHeGJ3N014NHBTQ0dRZ0p4cEkxdzVIaWxKUW5XQVlVQ2NPMHc2amlsWjBw
ZWxYaWxackRoY09QUW1kSUw4T0RDaUFnSUNBZ0lHRi93NXA4NHBXVVRjTzI0cFdwZEMvRG8rS1drbWZEc1JVQ2EwVkdMOE9idzdqRHNzS2x3cWNvd3JYRGhTdkN2c09ld3FmRG1BckRqVmwrS1hnSXdyNFc0cFdzTXkzRGwzSTRka0hpbEp4MzRvQ1g0cFNZ
dzR0RE9qVERqU3JEaXdvZ0lDQWdJQ0RpbG9nN2ZXRUp3NTNEaWxiaWxKdzg0cFdVNHBXVXdxM2lsWlFNNHBhU3dyL0N0M0Z4Tk1PcDRwU01YaVZPUE1PTnc1QVY0cFNzTkc3aWxwSGlscFBDcHlmaWdKY0RNOE9Td3I3RHA4T1N3NWJEb2xyRGxGM0NvY09o
QXk3RGpNT2l3NXpEdnNPUnc2N2lscURpbGFQRGpzT3N3NUY0WThLb0pjT3o0cFdhNHBXbVhrbGd3cmJDczhPcVJSVGlsSXhCd3JERHI4S3BJazlSYTN0OXdyQUI0cGFUNHBXbTRwUzBiY09MYjEvaWxMd0JZbjNEaytLVWpNT2t3clhEcDFiRHBzT09RdUtV
bE1LLzRwUzh3NkRDbzJRZnc3Y1Z3clFhUnNPR3c1ZkN1akxEbnVLV29NT0Z3N1hDdXhsZmUrS1VrQy9DdWdqaWxvVENxK0tXZ01LK084SzI0cFNBdzYvQ29rdkN1akhEcWNPOTRwV1VDQmxOdzRqaWxJQUo0cFNBd3FOd1pjS2lBaFREcnNPOTRwV1VkVm5E
cE1PdHc3bmlsWkREcEZWREt5QnN3NURpbEx6RGhpekRzZ2JpbEpoeHc2TERrc09wQlR2RHNNS3h3NmZDc1J0OVV1S1Ztc09Rd3FUQ3FzSy9kV0xEbW1Venc2ZkRyd3N4SE1PVndxc2N3NGZpbG9UaWxMeEp3N25DdWVLVXRNT0JSUUI5d3FmRHJNT3hGOE9L
dzVQQ3JpYkNweWZpbFpIRGhtRERrRlREcitLVXBNT2R4cEpST3g0U3c0UktRSGpEZ0duRHI4S3F3NFpZdzQzRHBjT1ZWTU9zSU9LVnFjT2RFT0tVdEFMaWxaMDFYbFlBdzRQRHNYUENxZUtWa1V6Q3NGMXFIUW5EcE9LVXJNU3g0cFMwQU9LVmwzSENwOE9x
NHBTTTRwV1JPc09tdzd0MkF6RWl3NU03Q2lBZ0lDQWdJTU95WW4vQ3NjT0llY085SThPNUZPS1ZyTU9id3JMaWxwSXJXeWZEa09LV2srS1doR2RpTzBiaWxwTlF3NmNmUitLVmtBSURBUUFCSnc9PQotLS0tLUVORCBQVUJMSUMgS0VZLS0tLS0=';

    return trim(preg_replace('/\s+/', '', $string));
  }

}
