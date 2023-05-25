<?php

namespace Drupal\az_vehicle\Form;

use Drupal\Core\Form\FormBase;
use Drupal\az_vehicle\VehicleService;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provies set your vehicle form.
 */
class SetYourVehicle extends FormBase {

  private $vehicle;

  /**
   * Class constructor.
   */
  public function __construct(VehicleService $vehicle) {
    $this->vehicle = $vehicle;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    return new static(
      $container->get('az_vehicle.vehicle')
    );
  }

  /**
   * Returns a unique string identifying the form.
   *
   * The returned ID should be a unique string that can be a valid PHP function
   * name, since it's used in hook implementation names such as
   * hook_form_FORM_ID_alter().
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'set_your_vehicle_form';
  }

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['year'] = [
      '#type' => 'select',
      '#title' => $this->t('Year'),
      '#attributes' => [
        'placeholder' => $this->t('1 | Year'),
      ],
      '#options' => _az_vehicle_get_year_options(),
      '#prefix' => '<div id="container-year" class="item">',
      '#suffix' => '</div>',
      '#ajax' => [
        'callback' => '::getMake',
        'effect' => 'fade',
        'event' => 'change',
        'disable-refocus' => FALSE,
        'wrapper' => 'container-make',
        'progress' => [
          'type' => 'throbber',
          'message' => NULL,
        ],
      ],
    ];

    $makeOptions = [];
  //  $this->getMakeList(2013);
    if ($selectedYear = $form_state->getValue('year')) {
      $makeOptions = $this->getMakeList($selectedYear);
    }
    $form['make'] = [
      '#type' => 'select',
      '#title' => $this->t('Make'),
      '#options' => $makeOptions,
      '#attributes' => [
        'placeholder' => $this->t('2 | Make'),
      ],
      '#prefix' => '<div id="container-make" class="item">',
      '#suffix' => '</div>',
      '#ajax' => [
        'callback' => '::getModel',
        'effect' => 'fade',
        'event' => 'change',
        'disable-refocus' => FALSE,
        'wrapper' => 'container-model',
        'progress' => [
          'type' => 'throbber',
          'message' => NULL,
        ],
      ],
    ];
    if (empty($selectedYear)) {
      $form['make']['#attributes']['disabled'] = TRUE;
    }

    $modelOptions = [];
    if ($selectedMake = $form_state->getValue('make')) {
       $modelOptions = $this->getModelList($selectedMake);
    }
    $form['model'] = [
      '#type' => 'select',
      '#title' => $this->t('Model'),
      '#attributes' => [
        'placeholder' => $this->t('3 | Model'),
      ],
      '#options' => $modelOptions,
      '#prefix' => '<div id="container-model" class="item">',
      '#suffix' => '</div>',
    ];
    if (empty($selectedMake)) {
      $form['model']['#attributes']['disabled'] = TRUE;
    }

    $form['engine'] = [
      '#type' => 'select',
      '#title' => $this->t('Engine'),
      '#options' => ['_none' => 'None'],
      '#attributes' => [
        'placeholder' => $this->t('4 | Engine'),
        'disabled' => TRUE,
      ],
      '#prefix' => '<div id="container-engine" class="item">',
      '#suffix' => '</div>',
    ];

    $form['#attached']['library'][] = 'az_vehicle/az_vehicle_asset';

    return $form;
  }

  public function getMake(array &$form, FormStateInterface $form_state) {
    return $form['make'];
  }

  public function getModel(array &$form, FormStateInterface $form_state) {
    return $form['model'];
  }

  /**
   * Get response data from api.
   *
   * @param $apiUrl
   * @param $query
   * @return array|mixed
   */
  public function getApiResponse($apiUrl, $query) {
    $settings = _az_vehicle_get_az_settings();
    $client = \Drupal::service('http_client_factory')->fromOptions([
      'base_uri' => $settings['api_base_uri'],
    ]);
    $response = $client->get($apiUrl, [
      'query' => $query,
    ]);

    $makes_results = Json::decode($response->getBody());
    return !empty($makes_results['Results']) ? $makes_results['Results'] : [];
  }

  /**
   * Get all manufactures.
   *
   * @return array
   */
  public function getAllManufacturers() {
    $settings = _az_vehicle_get_az_settings();
    $manufacturers = $this->getApiResponse($settings['api_all_manuf'], ['format' => 'json']);
    $list = [];
    foreach ($manufacturers as $manufacturer) {
      $list[$manufacturer['Mfr_ID']] = $manufacturer['Mfr_CommonName'];
    }

    return $list;
  }

  /**
   * Get all make from year.
   *
   * @param $year
   * @return array
   */
  public function getMakeList($year) {
    $settings = _az_vehicle_get_az_settings();
    $query = [
      'year' => $year,
      'format' => 'json',
    ];

    $data = NULL;
    $cid = 'az_vehicle_make_list:' . $year;

    $cacheService = \Drupal::service('cache.data');
    if ($cache = $cacheService->get($cid)) {
      $data = $cache->data;
    }
    else {
      $makes = [];
      $manufacturers = $this->getAllManufacturers();
      foreach ($manufacturers as $mId => $manufacturer) {
        $api = $settings['api_make'] . '/' . $mId;
        $result = $this->getApiResponse($api, $query);
        $makes = array_merge($makes, $result);
      }

      foreach ($makes as $make) {
        $data[$make['Make_ID']] = $make['Make_Name'];
      }

      $cacheService->set($cid, $data);
    }
// echo "<pre>"; print_r($makes);print_r($data);die;
    return $data;
  }

  /**
   * Get model list from make.
   *
   * @param $mamke
   * @return array
   */
  public function getModelList($make) {
    $settings = _az_vehicle_get_az_settings();
    $query = [
      'format' => 'json',
    ];

    $options = [];
    $models = $this->getApiResponse($settings['api_model'] . '/' . $make, $query);
    foreach ($models as $model) {
      $options[$model['Model_ID']] = $model['Model_Name'];
    }
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

}
