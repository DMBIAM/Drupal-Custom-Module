<?php

namespace Drupal\encryption\Form;

use Drupal\Core\Url;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\encryption\Services\v2_0\Encrypt\Encrypt;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Form to perform data decryption tests using as input a string of 
 * characters encrypted using Decrypt since KMS AWS SDK
 * Only test
 */
class DecryptionForm extends FormBase {

    /**
     * The encrypt method
     *
     * @var Drupal\encryption\Services\v2_0\Encrypt\Encrypt
     */
    protected $encrypt;

    /**
     * Constructs a new Form object.
     *
     * @param \Drupal\encryption\Services\v2_0\Encrypt\Encrypt $encrypt
     */
    public function __construct(Encrypt $encrypt) {
        $this->encrypt = $encrypt;
    }


    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container) {
        return new static(
            $container->get('encryption.v2_0.encrypt')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFormId() {
      return 'tbo_encrypt_test_form';
    }


    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state, $arg = []) {
        
        $url = Url::fromRoute('encryption.encrypt_settings');

        $form['filters'] = [
            '#type'  => 'container',
            '#title' => $this->t('Formulario para Test de descifrado'),
            '#open'  => true,
        ];

        $form['filters']['CiphertextBlob'] = [
            '#type' => 'textarea',
            '#title' => $this->t('Ingrese la cadena a descifrar'),
            '#rows' => 3,
            '#required' => TRUE,
            '#description' => $this->t('Debe asegurarse que el módulo <a href="@url" target="_blank">encryption</a>. se encuentre configurado, si no está configurado el descifrado no funcionara.', ['@url' => $url->toString()])

        ];

        $form['actions']['#type'] = 'actions';
        
        $form['actions']['submit'] = [
            '#type'  => 'submit',
            '#value' => $this->t('Descifrar'),
            '#button_type' => 'primary',
        ];
        
        return $form;
    }


    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state) {
        
        if ( $form_state->getValue('CiphertextBlob') == "") {
            $form_state->setErrorByName('CiphertextBlob', $this->t('Ingrese una cadena a descifrar.'));
        } 
        
    }

    /**
     * {@inheritdoc}
    */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        $field = $form_state->getValues();
        $CiphertextBlob = $field["CiphertextBlob"];
        if (!empty($CiphertextBlob)) {
            $plainText = $this->encrypt->doDecrypt($CiphertextBlob);
            $plainTextJson = json_encode($plainText);
            \Drupal::messenger()->addMessage($plainTextJson);
        }
    }
}