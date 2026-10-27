<?php

namespace Drupal\developer_directory\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\developer_directory\Service\DeveloperDirectoryManager;
use Override;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DeveloperSearchForm extends FormBase{

    protected DeveloperDirectoryManager $directoryManager;

    public function __construct(DeveloperDirectoryManager $directoryManager)
    {
        $this->directoryManager = $directoryManager;
    }

    public static function create(ContainerInterface $container): static{
        return new static($container->get('developer_directory.manager'));
    }

    /**
     * Identificador único del formulario
     */    
    public function getFormId(): string
    {
        return 'developer_directory_search_form';
    }

    /**
     * Construye el formulario
     */
    
    public function buildForm(array $form, FormStateInterface $form_state): array
    {
        /**
         * textfield
         * hidden
         * url
         * textarea
         * select
         * 
         */
        $form['name'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Nombre del desarrollador'),
            '#description' => $this->t('Introduce un nombre o parte de él.'),
            '#default_value' => $form_state->getValue('name', '')
        ];

        $form['actions'] = [
            '#type' => 'actions'
        ];

        $form['actions']['submit'] = [
            '#type' => 'submit',
            '#value' => $this->t('Buscar')
        ];

        // si se ha enviado el formualrio, mostramos los resultados
        if($form_state->get('search_submitted')){
            $query = (string) $form_state->getValue('name', '');
            $developers = $this->directoryManager->findByName($query);

            $items = [];

            foreach($developers as $developer){
                $items[] = $developer['name'] . ' - ' . $developer['email'];
            }

            $form['results'] = [
                '#theme' => 'item_list',
                '#title' => $this->t('Resultados'),
                '#items' => $items,
                '#empty' => $this->t('No se han encontrado desarrolladores.')
            ];          
        }

        return $form;
    }

    /**
     * Valida los datos recibidos
     */    
    public function validateForm(array &$form, FormStateInterface $form_state): void
    {
        $name = trim((string) $form_state->getValue('name'));

        if(strlen($name) > 10){
            $form_state->setErrorByName('name', $this->t('El nombre no puede superar los 10 caracteres.'));
        }
    }

    /**
     * Procesar el envío
     */    
    public function submitForm(array &$form, FormStateInterface $form_state): void
    {
        $form_state->set('search_submitted', TRUE);
        
        $form_state->setRebuild();
    }
}