<?php 

declare(strict_types = 1);

namespace Drupal\developer_directory\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\developer_directory\Service\DeveloperNotificationQueue;
use Drupal\node\NodeInterface;

final class DeveloperEntityHooks{

    public function __construct(private readonly DeveloperNotificationQueue $notificationQueue)
    { }

    #[Hook('node_presave')]
    public function normalizedEmail(NodeInterface $node): void{
        if($node->bundle() !== 'developer' || $node->get('field_email')->isEmpty()) return;

        $email = mb_strtolower(trim($node->get('field_email')->value));
        $node->set('field_email', $email);
    }

    #[Hook('node_insert')]
    public function enqueueNotification(NodeInterface $node): void{
        if($node->bundle() !== 'developer') return;

        $this->notificationQueue->enqueue((int) $node->id(), 'created');
    }

    #[Hook('node_update')]
    public function enqueueUpdateNotification(NodeInterface $node): void{
        if($node->bundle() !== 'developer') return;

        $this->notificationQueue->enqueue((int) $node->id(), 'updated');
    }
}