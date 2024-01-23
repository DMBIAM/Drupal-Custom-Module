<?php

declare(strict_types=1);

namespace Drupal\encryption\Services\v2_0\Encrypt;

use Drupal\encryption\Services\v2_0\Encrypt\Exceptions\ErrorCreateEncryptClientException;
use Symfony\Component\HttpFoundation\Response;
use Aws\Exception\{AwsException, CredentialsException};

/**
 * @author bitsJuan.Diaz
 */
class Encrypt
{

  final const HTTP_ERROR_MESSAGE = 'Se presentó un error interno, por favor intente más tarde';

  public function __construct(protected readonly EncryptKmsFactory $clientFactory)
  {
  }

  public function doEncrypt(string $plainText): array
  {
    try {
      $kms = $this->kmsConfig();
      $kmsClient = $kms->kmsClient();

      $result = $kmsClient->encrypt([
        'EncryptionAlgorithm' => $kms->encryptionAlgorithm(),
        'KeyId' => $kms->arn(),
        'Plaintext' => $plainText,
      ]);

      return [
        'status' => Response::HTTP_OK,
        'CipherTextBlobFormat' => 'base64',
        'EncryptionAlgorithm' => $result->get('EncryptionAlgorithm'),
        'CipherTextBlob' => base64_encode($result->get('CiphertextBlob')),
      ];

    } catch (AwsException|CredentialsException|\LogicException|ErrorCreateEncryptClientException $e) {
      $this->errorLog($e->getMessage());
      return [
        'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
        'error' => self::HTTP_ERROR_MESSAGE,
      ];
    }
  }

  public function doDecrypt(string $cipherText): array
  {
    try {
      $kms = $this->kmsConfig();
      $kmsClient = $kms->kmsClient();

      $result = $kmsClient->decrypt([
        'CiphertextBlob' => base64_decode($cipherText),
        'KeyId' => $kms->arn(),
        'EncryptionAlgorithm' => $kms->encryptionAlgorithm(),
      ]);

      return [
        'status' => Response::HTTP_OK,
        'PlainTextFormat' => 'string',
        'EncryptionAlgorithm' => $result->get('EncryptionAlgorithm'),
        'Plaintext' => $result->get('Plaintext'),
      ];

    } catch (AwsException|CredentialsException|\LogicException|ErrorCreateEncryptClientException $e) {
      $this->errorLog($e->getMessage());
      return [
        'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
        'error' => self::HTTP_ERROR_MESSAGE,
      ];
    }
  }

  /**
   *  The public key retrive from KMS AWS.
   *
   * @seen using-constants-for-http-status-code /Symfony/http-foundation/Response.php
   */
  public function getPublicKey(): array
  {
    try {
      $kms = $this->kmsConfig();
      $kmsClient = $kms->kmsClient();

      $awsResult = $kmsClient->getPublicKey([
        'KeyId' => $kms->arn(),
      ]);

      $key ="-----BEGIN PUBLIC KEY-----\n". base64_encode($awsResult->get('PublicKey')). "\n-----END PUBLIC KEY-----";

      $response = [];
      // HEREDOC
      $str = sprintf(<<<'KEY'
      %s
      KEY, $key);
      $str = trim($str);
      $kms->isTestMode() && $response['testMode'] = TRUE;
      $response['status'] = Response::HTTP_OK;
      $response['format'] = 'base64';
      $response['key'] = base64_encode($str);
      return $response;

    } catch (AwsException|CredentialsException|\LogicException|ErrorCreateEncryptClientException $e) {
      $this->errorLog($e->getMessage());
      return [
        'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
        'error' => self::HTTP_ERROR_MESSAGE,
      ];
    }
  }

  protected function errorLog(string $message): void
  {
    \Drupal::logger('encryption')->error($message);
  }

  protected function kmsConfig(): Kms
  {
    return $this->clientFactory->__invoke();
  }

}
