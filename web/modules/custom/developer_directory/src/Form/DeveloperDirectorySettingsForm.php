<?php

declare(strict_types = 1);

namespace Drupal\developer_directory\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

final class DeveloperDirectorySettingsForm extends ConfigFormBase {

	public function getFormId()
    {
        return 'developer_directory_settings';
    }
    
    public function getEditableConfigNames()
    {
        return ['developer_directory.settings'];
    }
    
    public function buildForm(array $form, FormStateInterface $form_state): array
    {
        $form['directory_enabled'] = [
            '#type' => 'checkbox',
            '#title' => $this->t('Directorio público activo'),
            '#config_target' => 'developer_directory.settings:directory_enabled',
        ];

        $form['page_title'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Título de la página del directorio'),
            '#required' => TRUE,
            '#config_target' => 'developer_directory.settings:page_title',
        ];

        $form['items_per_page'] = [
            '#type' => 'number',
            '#title' => $this->t('Desarrolladores por página'),
            '#config_target' => 'developer_directory.settings:items_per_page',
        ];

        $form['notification_email'] = [
            '#type' => 'email',
            '#title' => $this->t('Email de avisos'),
            '#description' => $this->t('Vacío = sin avisos.'),
            '#config_target' => 'developer_directory.settings:notification_email',
        ];

        return parent::buildForm($form, $form_state);
    }

}