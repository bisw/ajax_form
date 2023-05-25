<?php

namespace Drupal\az_vehicle;

use Drupal\Core\Http\ClientFactory;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Vehicle helper service.
 */
class VehicleService {

  use StringTranslationTrait;

  /**
   * The http client.
   *
   * @var \Drupal\Core\Http\ClientFactory
   */
  protected $httpClient;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Get car configuration.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * Creates a verbose messenger.
   */
  public function __construct(ClientFactory $http_client_factory, ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
    $this->httpClient = $http_client_factory;
    $this->config = $config_factory->get('vehicle_config.settings');
  }

  public function getResponse($apiUrl, $query) {
    $client = $this->httpClient->fromOptions([
      'base_uri' => $this->config->get('api_base_uri'),
    ]);
    $response = $client->get($apiUrl, [
      'query' => $query,
    ]);

    $results = Json::decode($response->getBody());
    return !empty($results['Results']) ? $results['Results'] : [];
  }

  public function getAllManufacturers() {
    $manufs = $this->getResponse($this->config->get('api_all_manuf'), ['format' => 'json']);
    $list = [];
    foreach ($manufs as $manuf) {
      $list[$manuf['Mfr_ID']] = $manuf['Mfr_CommonName'];
    }

    return $list;
  }

  public function getMake($year) {
    $makes = [];
    $query = [
      'year' => $year,
      'format' => 'json',
    ];
    $manufacturers = $this->getAllManufacturers();
    foreach ($manufacturers as $mId => $manufacturer) {
      $api = $this->config->get('api_make') . '/' . $mId;
      $result = $this->getResponse($api, $query);
      $makes = array_merge($makes, $result);
    }

    $options = [];
    foreach ($makes as $make) {
      $options[$make['Make_ID']] = $make['Make_Name'];
    }

    return $options;
  }

  public function getModel($make) {
    $options = [];
    $query = [
      'format' => 'json',
    ];
    $modelApi = $this->config->get('api_model');
    $models = $this->getResponse($modelApi . '/' . $make, $query);
    foreach ($models as $model) {
      $options[$model['Model_ID']] = $model['Model_Name'];
    }

    return $options;
  }

}
