<?php

namespace Drupal\encryption\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form to configure public keys.
 */
class EncryptionSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'tbo_encrypt.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'tbo_encrypt_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $config = $this->config('tbo_encrypt.settings');
    $group_encrypt = "encrypt_settings";

    $form[$group_encrypt] = [
      '#type' => 'details',
      '#title' => $this->t('Configuraciones de cifrado'),
      '#open' => TRUE,
      '#group' => 'bootstrap',
      '#weight' => 4,
    ];

    $form[$group_encrypt]['authenticationIam'] = [
      '#type' => 'checkbox',
      '#title' => $this->t("¿ Habilitar autenticación mediante roles IAM ?"),
      '#default_value' => (bool)$config->get('authenticationIam'),
      '#return_value' => TRUE,
      '#attributes' => [
        'id' => 'check_autentication_iam',
      ],
      '#description' => $this->t('Si esta opción se habilita, la autenticación hacia AWS se realizará mediante roles IAM desde el servidor, de lo contrario se habilitara la opción de autenticación por secret y access key.')
    ];

    $form[$group_encrypt]['accessKey'] = [
      '#type' => 'key_select',
      '#title' => $this->t('accessKey'),
      '#empty_option' => $this->t('- Select -'),
      '#default_value' => $config->get('accessKey'),
      '#key_filters' => ['type' => 'authentication'],
      '#description' => $this->t('Select a accessKey since module key of Drupal.'),
      '#states' => [
        'visible' => [
          ':input[id="check_autentication_iam"]' => ['checked' => FALSE],
        ],
        'required' => [
          ':input[id="check_autentication_iam"]' => ['checked' => FALSE],
        ],
      ],
    ];

    $form[$group_encrypt]['secretKey'] = [
      '#type' => 'key_select',
      '#title' => $this->t('secretKey '),
      '#empty_option' => $this->t('- Select -'),
      '#default_value' => $config->get('secretKey'),
      '#key_filters' => ['type' => 'authentication'],
      '#description' => $this->t('Select a secretKey since module key of Drupal.'),
      '#states' => [
        'visible' => [
          ':input[id="check_autentication_iam"]' => ['checked' => FALSE],
        ],
        'required' => [
          ':input[id="check_autentication_iam"]' => ['checked' => FALSE],
        ],
      ],
    ];

    $form[$group_encrypt]['arn'] = [
      '#type' => 'key_select',
      '#title' => $this->t('ARN KMS AWS'),
      '#empty_option' => $this->t('- Select -'),
      '#default_value' => $config->get('arn'),
      '#key_filters' => ['type' => 'authentication'],
      '#description' => $this->t('Select a key containing the KMS AWS ARN.'),
    ];

    $form[$group_encrypt]['encryptionAlgorithm'] = [
      '#type' => 'select',
      '#title' => $this->t('Encryption Algorithm'),
      '#default_value' => $config->get('encryptionAlgorithm') != '' ? $config->get('encryptionAlgorithm') :'RSAES_OAEP_SHA_256',
      '#options' => [
        'SYMMETRIC_DEFAULT' => 'SYMMETRIC DEFAULT',
        'RSAES_OAEP_SHA_1' => 'RSAES OAEP SHA 1',
        'RSAES_OAEP_SHA_256' => 'RSAES OAEP SHA 256',
        'SM2PKE' => 'SM2PKE',
      ],
    ];

    $form[$group_encrypt]['version'] = [
      '#type' => 'textfield',
      '#title' => $this->t("AWS Version"),
      '#default_value' => $config->get('version') != '' ? $config->get('version') : '2014-11-01',
    ];

    $form[$group_encrypt]['region'] = [
      '#type' => 'textfield',
      '#title' => $this->t("AWS region"),
      '#default_value' => $config->get('region') != '' ? $config->get('region') : 'us-east-1',
    ];

    $group_test = "test_settings";

    $form[$group_test] = [
      '#type' => 'details',
      '#title' => $this->t('Configuraciones modo de pruebas'),
      '#open' => TRUE,
      '#group' => 'bootstrap',
      '#weight' => 4,
    ];

    $form[$group_test]['test_mode'] = [
      '#type' => 'checkbox',
      '#title' => $this->t("¿ Habilitar modo pruebas ?"),
      '#default_value' => $config->get('test_mode') != '' ? $config->get('test_mode') : 0,
      '#return_value' => TRUE,
      '#description' => $this->t('Esta opción deshabilita el proceso de cifrado/descifrado y le indica al front mediante el xapi donde se retorna la llave pública que no cifre la información enviada al backend. Precaución, no utilizar esta opción en ambientes productivos')
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('tbo_encrypt.settings')
      ->set('authenticationIam', $form_state->getValue('authenticationIam'))
      ->set('accessKey', $form_state->getValue('accessKey'))
      ->set('secretKey', $form_state->getValue('secretKey'))
      ->set('arn', $form_state->getValue('arn'))
      ->set('encryptionAlgorithm', $form_state->getValue('encryptionAlgorithm'))
      ->set('version', $form_state->getValue('version'))
      ->set('region', $form_state->getValue('region'))
      ->set('test_mode', $form_state->getValue('test_mode'))
      ->save();
  }

}
