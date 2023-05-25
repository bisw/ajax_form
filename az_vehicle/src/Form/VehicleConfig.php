<?php

namespace Drupal\az_vehicle\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provide configaration for vehicle.
 */
class VehicleConfig extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'vehicle_config.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'vehicle_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('vehicle_config.settings');

    $form['api'] = [
      '#type' => 'details',
      '#title' => $this->t('API details'),
      '#open' => FALSE,
    ];
    $form['api']['api_base_uri'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API base url'),
      '#default_value' => $config->get('api_base_uri'),
      '#description' => $this->t('API base url. Put "/" after api url.'),
    ];
    $form['api']['api_all_manuf'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API for manufactures'),
      '#default_value' => $config->get('api_all_manuf'),
      '#description' => $this->t('API for manufactures. Do not put "/" before or after api url.'),
    ];
    $form['api']['api_make'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API for make'),
      '#default_value' => $config->get('api_make'),
      '#description' => $this->t('API for make. Do not put "/" before or after api url.'),
    ];
    $form['api']['api_model'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API model url'),
      '#default_value' => $config->get('api_model'),
      '#description' => $this->t('API model url. Do not put "/" before or after api url.'),
    ];
    $form['api']['api_engine'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API for engine'),
      '#default_value' => $config->get('api_engine'),
      '#description' => $this->t('API for engine. Do not put "/" before or after api url.'),
    ];

    $form['year'] = [
      '#type' => 'details',
      '#title' => $this->t('Year details'),
      '#open' => FALSE,
    ];
    $form['year']['form_year_last'] = [
      '#type' => 'number',
      '#title' => $this->t('Recent year'),
      '#default_value' => $config->get('form_year_last'),
      '#description' => $this->t('Recent year.'),
    ];
    $form['year']['form_year_range'] = [
      '#type' => 'number',
      '#title' => $this->t('Range for year'),
      '#default_value' => $config->get('form_year_range'),
      '#description' => $this->t('Range for year.'),
    ];
    $form['year']['form_year_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Form title'),
      '#default_value' => $config->get('form_year_title'),
      '#description' => $this->t('Form title.'),
    ];
    $form['year']['form_year_des'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Form description'),
      '#default_value' => $config->get('form_year_des'),
      '#description' => $this->t('Form description.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $this->config('vehicle_config.settings')
      ->set('api_base_uri', $form_state->getValue('api_base_uri'))
      ->set('api_all_manuf', $form_state->getValue('api_all_manuf'))
      ->set('api_make', $form_state->getValue('api_make'))
      ->set('api_model', $form_state->getValue('api_model'))
      ->set('api_engine', $form_state->getValue('api_engine'))
      ->set('form_year_last', $form_state->getValue('form_year_last'))
      ->set('form_year_range', $form_state->getValue('form_year_range'))
      ->set('form_year_title', $form_state->getValue('form_year_title'))
      ->set('form_year_des', $form_state->getValue('form_year_des'))
      ->save();
  }

}
