<?php

namespace Drupal\az_vehicle\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the set your vehicle block.
 *
 * @Block(
 *   id = "set_your_vehicle_block",
 *   admin_label = @Translation("Set Your Vehicle"),
 *   category = @Translation("Custom")
 * )
 */
class SetYourVehicleBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * {@inheritdoc}
   */
  public function __construct(FormBuilderInterface $form_builder) {
    $this->formBuilder = $form_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('form_builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $settings = _az_vehicle_get_az_settings();
    return [
      '#theme' => 'set_your_vehicle',
      '#title' => $settings['title'],
      '#des' => $settings['des'],
      '#form' => $this->formBuilder->getForm('Drupal\az_vehicle\Form\SetYourVehicle'),
    ];
  }

}
