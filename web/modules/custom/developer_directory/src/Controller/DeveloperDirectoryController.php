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
      $items[] = $developer['name'] . " - " . $developer['email'];
    }

    $build['content'] = [
      '#theme' => 'item_list',
      '#title' => $this->t('Directorio de desarrolladores'),
      '#items' => $items
    ];

    return $build;
  }

  #[Route(
    '/developers/{name}',
    name: 'developer_directory.view',
    requirements: [
      '_permission' => 'access content',
      'name' => 'David|Rubén|Airam|Antonio|Elí|Yesica|Cristian'
    ]
  )]
  public function view(string $name): array {
    $developers = [
      'David' => [
        'role' => "Fullstack developer",
        'experience' => '20 years',
        'skills' => ['HTML', 'CSS', 'JavaScript'],
      ],
      'Rubén' => [
        'role' => "Frontend developer",
        'experience' => 15,
        'skills' => ['HTML', 'CSS', 'JavaScript'],
      ],
      'Airam' => [
        'role' => "DevOps",
        'experience' => 5,
        'skills' => ['HTML', 'CSS', 'JavaScript'],
      ],
      'Antonio' => [
        'role' => "Frontend developer",
        'experience' => 15,
        'skills' => ['HTML', 'CSS', 'JavaScript'],
      ],
      'Elí' => [
        'role' => "DevOps",
        'experience' => 5,
        'skills' => ['HTML', 'CSS', 'JavaScript'],
      ],
      'Yesica' => [
        'role' => "Frontend developer",
        'experience' => 15,
        'skills' => [],
      ],
      'Cristian' => [
        'role' => "DevOps",
        'experience' => 5,
        'skills' => ['HTML', 'CSS', 'JavaScript'],
      ],
    ];

    if(!isset($developers[$name])) throw new NotFoundHttpException();
    $developer = $developers[$name];

    return [
      '#theme' => 'developer_profile',
      '#name' => $name,      
      '#role' => $developer['role'],
      '#experience' => $developer['experience'],
      '#skills' => $developer['skills']
    ];
  }

  public function admin(): array{
    return [
      '#markup' => $this->t('Página de administración del directorio de desarrolladores')
    ];
  }
}
