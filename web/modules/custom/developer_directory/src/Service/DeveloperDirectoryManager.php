<?php

namespace Drupal\developer_directory\Service;

/**
 * Gestiona las operaciones del directorio de desarrolladores
 * 
 * De momento trabajamos con datos de ejemplo.
 * El día 3 estudiaremos el almacenamiento con bbdd
 */
class DeveloperDirectoryManager {
    public function getDevelopers(): array {
        return [
            [
                'name' => 'David',
                'email' => 'davelop@gmail.com'
            ],
            [
                'name' => 'Carlos',
                'email' => 'carlos@gmail.com'
            ],
            [
                'name' => 'Berto',
                'email' => 'berto@gmail.com'
            ],
        ];
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