<?php

namespace Drupal\developer_directory\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\developer_directory\Service\DeveloperDirectoryManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Displays a list of featured developers.
 */
#[Block(
  id: 'developer_directory_featured',
  admin_label: new TranslatableMarkup('Developers destacados'),
)]
class FeaturedDevelopersBlock extends BlockBase implements ContainerFactoryPluginInterface {
  
  public function __construct(array $configuration, $plugin_id, $plugin_definition, protected DeveloperDirectoryManager $directoryManager,) {
        parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition,): static {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('developer_directory.manager'),
    );
  }

  public function defaultConfiguration(): array {
    return [
        'number_of_developers' => 5,
    ] + parent::defaultConfiguration();
  }

  public function blockForm($form, FormStateInterface $form_state,): array {
    $form = parent::blockForm($form, $form_state);

    $form['number_of_developers'] = [
      '#type' => 'number',
      '#title' => $this->t('Número de desarrolladores'),
      '#default_value' => $this->configuration['number_of_developers'],
      '#min' => 1,
      '#max' => 20,
      '#required' => TRUE,
    ];

    return $form;
  }

  public function blockSubmit($form, FormStateInterface $form_state): void {
    parent::blockSubmit($form, $form_state);

    $this->configuration['number_of_developers'] = (int) $form_state->getValue('number_of_developers');
  }

  public function build(): array {
    $developers = $this->directoryManager->getDevelopers($this->configuration['number_of_developers']);

    $items = [];

    foreach ($developers as $developer) {
      $items[] = [
        '#type' => 'link',
        '#title' => $developer->getTitle(),
        '#url' => $developer->toUrl(),
      ];
    }

    return [
      '#theme' => 'item_list',
      '#title' => $this->t('Developers destacados'),
      '#items' => $items,
      '#cache' => [
        'tags' => ['node_list'],
        'contexts' => ['user.permissions'],
      ],
    ];
  }
}