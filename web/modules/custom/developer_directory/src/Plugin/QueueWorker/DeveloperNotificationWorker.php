<?php

namespace Drupal\developer_directory\Plugin\QueueWorker;

use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\Attribute\QueueWorker;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Override;
use Symfony\Component\DependencyInjection\ContainerInterface;

#[QueueWorker(
    id: 'developer_directory_notifications',
    title: new TranslatableMarkup('Notificaciones de desarrolladores'),
    cron: ['time' => 30]
)]
final class DeveloperNotificationWorker extends QueueWorkerBase implements ContainerFactoryPluginInterface {    
    
    public function __construct(array $configuration, $plugin_id, $plugin_definition, protected LoggerChannelInterface $logger)
    {
        return parent::__construct($configuration, $plugin_id, $plugin_definition);        
    }

    public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static{
        return new static(
            $configuration,
            $plugin_id,
            $plugin_definition,
            $container->get('logger.factory')->get('developer_directory')
        );
    }
    
    public function processItem($data): void
    {
        //Comprobamos que los datos recibidos son válidos.
        if(!is_array($data) || empty($data['developer_id'])){
            $this->logger->warning('Se ha recibido una notificación sin ID de desarrollador.');
            return;
        }

        $developer_id = (int) $data['developer_id'];
        $operation = $data['operation'] ?? 'unknown';

        // En un proyecto real, aquí cargaríamos la entidad y enviaríamos el correo , o generariamos el pdf, o llamaríamos a un api externa...

        $this->logger->info('Procesada la notificación del desarrollador @id. Operación: @operation',[
            '@id' => $developer_id,
            '@operation' => $operation
        ]);
    }
}