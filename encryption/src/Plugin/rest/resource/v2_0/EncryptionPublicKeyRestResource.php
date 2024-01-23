<?php

namespace Drupal\encryption\Plugin\rest\resource\v2_0;

use Drupal\rest\Annotation\RestResource;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\encryption\Services\v2_0\EncryptionRestLogic;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *
 * @RestResource(
 *   api_response_version = "v2_0",
 *   id = "encryption_v2_0_public_key_rest_resource",
 *   label = @Translation("TBO Encryption - Public Key"),
 *   uri_paths = {
 *    "canonical" = "/api/v2.0/convergent/key/public",
 *   }
 * )
 *
 * @author bitsJuan.Diaz
 *
 */
class EncryptionPublicKeyRestResource extends ResourceBase
{

  protected EncryptionRestLogic $encryptionV2RestLogic;

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition)
  {
    $self = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $self->encryptionV2RestLogic = $container->get('encryption.v2_0.encryption_rest_logic');
    return $self;
  }

  public function get(): ResourceResponse
  {
    $response = $this->encryptionV2RestLogic->getPublicKey();
    return new ResourceResponse($response);
  }

}
