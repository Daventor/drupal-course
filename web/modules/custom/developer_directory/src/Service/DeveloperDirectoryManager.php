<?php

namespace Drupal\developer_directory\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;

/**
 * Gestiona las operaciones del directorio de desarrolladores
 * 
 * De momento trabajamos con datos de ejemplo.
 * El día 3 estudiaremos el almacenamiento con bbdd
 */
class DeveloperDirectoryManager {
    public function __construct(protected EntityTypeManagerInterface $entityManager)
    { }
    
    public function getDevelopers(): array {
        $storage = $this->entityManager->getStorage('node');

        $ids = $storage->getQuery()
            ->accessCheck(true)
            ->condition('type', 'developer')
            ->condition('status', 1)
            ->sort('title', 'ASC')
            ->execute();
        
        return $storage->loadMultiple($ids);
    }

    public function getDeveloper(string $id): ?NodeInterface{
        $node = $this->entityManager->getStorage('node')->load($id);

        if(!$node instanceof NodeInterface){
            return null;
        }

        if($node->bundle() !== 'developer' || !$node->access('view')){
            return null;
        }

        return $node;
    }

    
    public function findByName(string $query): array{
        $matches = [];
        $query = trim($query);

        foreach($this->getDevelopers() as $developer){
            if($query === '' || stripos($developer['name'], $query) !== FALSE){
                $matches[] = $developer;
            }
        }

        return $matches;
    }

    public function processRegistration(array $data): array{
        return[
            'name' => trim($data['name']),
            'email' => trim($data['email']),
            'portfolio' => trim($data['portfolio'] ?? ''),
            'experience' => $data['experience'],
            'technologies' => $data['technologies'] ?? [],
            'available' => !empty($data['available']),
            'comments' => trim($data['comments'] ?? ''),            
        ];
    }

}