<?php

declare(strict_types = 1);

namespace Drupal\developer_directory\EventSubscriber;

use Drupal\Core\Config\ConfigCrudEvent;
use Drupal\Core\Config\ConfigEvents;
use Drupal\Core\Session\AccountProxyInterface;
use Override;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class SettingsAuditSubscriber implements EventSubscriberInterface {
    public function __construct(public readonly LoggerInterface $logger, private readonly AccountProxyInterface $currentUser)
    { } 
        
    public static function getSubscribedEvents(): array
    {
        return [ConfigEvents::SAVE => 'onConfigSave'];
    }

    public function onConfigSave(ConfigCrudEvent $event): void{
        $config = $event->getConfig();

        if($config->getName() !== 'developer_directory.settings') return;

        $changed = array_filter(
            array_keys($config->getRawData()),
            fn (string $key): bool => $event->isChanged($key),
        );

        if(!$changed) return;

        $this->logger->notice('@user ha cambiado la configuración del directorio: @keys', 
            [
                '@user' => $this->currentUser->getAccountName() ?: 'sistema',
                '@keys' => implode(', ', $changed),
            ]
        );
    }
}