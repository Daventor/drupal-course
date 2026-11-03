<?php

namespace Drupal\developer_directory\Service;

use Drupal\Core\Queue\QueueFactory;

final class DeveloperNotificationQueue{
    public function __construct(private QueueFactory $queueFactory)
    { }

    public function enqueue(int $developerId, string $operation): void {
        $queue = $this->queueFactory->get('developer_directory_notifications');

        // Guardamos los datos necesarios para procesar el trabajo en el futuro
        $queue->createItem([
            'developer_id' => $developerId,
            'operation' => $operation
        ]);
    }
}