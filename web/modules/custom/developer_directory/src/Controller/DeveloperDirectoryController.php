<?php

declare(strict_types=1);

namespace Drupal\developer_directory\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\developer_directory\Service\DeveloperDirectoryManager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Symfony\Component\Routing\Attribute\Route;

/**
 * Returns responses for Developer Directory routes.
 */
final class DeveloperDirectoryController extends ControllerBase {
  
  /**
   * Servicio que gestiona el directorio.
   *
   * @var \Drupal\developer_directory\Service\DeveloperDirectoryManager
   */
  protected DeveloperDirectoryManager $directoryManager;

  /**
   * Constructor.
   */
  public function __construct(DeveloperDirectoryManager $directory_manager) {
    $this->directoryManager = $directory_manager;
  }

  /**
   * Crea el controlador utilizando el contenedor de Drupal.
   */
  public static function create(ContainerInterface $container): static {
    return new static($container->get('developer_directory.manager'));
  }

  public function list(): array {
    $developers = $this->directoryManager->getDevelopers();

    $items = [];
    foreach($developers as $developer){
      $items[] = [
        '#type' => 'link',
        '#title' => $developer->getTitle() . " - " . $developer->get('field_email')->value,
        '#url' => \Drupal\Core\Url::fromRoute('developer_directory.view', ['id' => $developer->id()])
      ];

      $items[] = [
        '#type' => 'link',
        '#title' => 'Editar',
        '#url' => \Drupal\Core\Url::fromRoute('developer_directory.edit', ['developer' => $developer->id()])
      ];
    }

    $build['content'] = [
      '#theme' => 'item_list',
      '#title' => $this->t('Directorio de desarrolladores'),
      '#items' => $items
    ];

    return $build;
  }

  #[Route(
    '/developers/{id}',
    name: 'developer_directory.view',
    requirements: [
      '_permission' => 'access content',
      'id' => '\d+',
    ]
  )]
  public function view(int $id): array {
    $developer = $this->directoryManager->getDeveloper($id);

    if(!$developer) throw new NotFoundHttpException(); 

    $experienceField = $developer->get('field_experience');
    $experienceOptions = $experienceField->getFieldDefinition()->getSetting('allowed_values');
    $experienceLabel = $experienceOptions[$experienceField->value] ?? $experienceField->value;    

    return [
      '#theme' => 'developer_profile',
      '#name' => $developer->getTitle(),      
      '#email' => $developer->get('field_email')->value,
      '#bio' => $developer->get('field_bio')->value,
      '#experience' => $experienceLabel,
      '#portfolio_title' => $developer->get('field_portfolio')->title,      
      '#portfolio_uri' => $developer->get('field_portfolio')->uri,
      '#available' => (bool) $developer->get('field_available')->value,
      '#skills' => $developer->get('field_technologies')->getValue()
      //'#skills' => array_column($developer->get('field_technologies')->getValue(), 'value')
    ];
  }

  public function admin(): array{
    return [
      '#markup' => $this->t('Página de administración del directorio de desarrolladores')
    ];
  }
}
