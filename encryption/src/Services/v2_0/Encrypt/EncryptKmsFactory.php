<?php

declare(strict_types=1);

namespace Drupal\encryption\Services\v2_0\Encrypt;

use Aws\Credentials\Credentials;
use Aws\Kms\KmsClient;
use Drupal\Component\Plugin\Exception\{InvalidPluginDefinitionException, PluginNotFoundException};
use Drupal\Core\Config\{Config, ConfigFactory, ImmutableConfig};
use Drupal\encryption\Services\v2_0\Encrypt\Exceptions\ErrorCreateEncryptClientException;
use Drupal\payment\Services\PaymentUtilsServices;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

/**
 * @author bitsJuan.Diaz
 */
class EncryptKmsFactory
{

  protected readonly Config|ImmutableConfig $config;

  public function __construct(protected readonly ConfigFactory $configFactory, protected readonly PaymentUtilsServices $utilsServices)
  {
    $this->config = $this->configFactory->get('tbo_encrypt.settings');
  }

  /**
   * @throws \RuntimeException|\LogicException
   */
  public function __invoke(): Kms
  {
    if (empty($this->config->getRawData())) {
      throw ErrorCreateEncryptClientException::valueNotFound('data config not found!');
    }

    $arnName = $this->config->get('arn');
    $arn = $this->getKeyMachineFromKey($arnName) ?? throw ErrorCreateEncryptClientException::keyNotFound($arnName, 'ARN');

    $encryptionAlgorithm = $this->config->get('encryptionAlgorithm');
    $version             = $this->config->get('version');
    $region              = $this->config->get('region');
    $authenticationIam   = (bool)$this->config->get('authenticationIam');
    $testMode            = (bool)$this->config->get('test_mode');

    $kmsClient = $this->kmsClient($version, $region, $authenticationIam);
    return new Kms($arn, $encryptionAlgorithm, $testMode, $kmsClient);
  }

  protected function kmsClient(
    string $version,
    string $region,
    bool   $authenticationIam
  ): KmsClient
  {

    $credentials = $this->profileCredentials($authenticationIam);

    $config['version'] = $version;
    $config['region']  = $region;

    if (!empty($credentials)) {
      $credentials && $config['credentials'] = $credentials;
    }
    
    return new KmsClient($config);
  }

  protected function profileCredentials(bool $authenticationIam): array
  {

    $credentials = [];

    if (!$authenticationIam) {
      $credentials = $this->credentials();
    }

    return $credentials instanceof Credentials ? $credentials->toArray() : $credentials;
  }

  protected function credentials(): Credentials
  {
    $accessKeyName = $this->config->get('accessKey');
    $secretKeyName = $this->config->get('secretKey');

    $accessKey = $this->getKeyMachineFromKey($accessKeyName) ?? throw ErrorCreateEncryptClientException::keyNotFound($accessKeyName, 'accessKey');
    $secretKey = $this->getKeyMachineFromKey($secretKeyName) ?? throw ErrorCreateEncryptClientException::keyNotFound($secretKeyName, 'secretKey');

    return new Credentials($accessKey, $secretKey);
  }

  protected function getKeyMachineFromKey(string $key): ?string
  {
    try {
      return $this->utilsServices->getKeyMachine($key);
    } catch (InvalidPluginDefinitionException|PluginNotFoundException $e) {
      throw new RuntimeException($e->getMessage());
    }

  }

}
