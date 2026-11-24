<?php

/** 
 * Crea la configuración por defecto de developer_directory.settings.
 */
function developer_directory_post_update_create_settings(): void{
    $config = \Drupal::configFactory()->getEditable('developer_directory.settings');

    if(!$config->isNew()){
        return;
    }

    $config->setData([
        'langcode' => \Drupal::languageManager()->getDefaultLanguage()->getId(),
        'directory_enabled' => true,
        'page_title' => 'Directorio de desarrolladores',
        'items_per_page' => 10,
        'notification_email' => '',
    ])->save();
}