<?php

namespace Drupal\developer_directory\Form;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\developer_directory\Service\DeveloperDirectoryManager;
use Drupal\node\NodeInterface;
use Override;

class DeveloperForm extends FormBase {

    public function __construct(protected DeveloperDirectoryManager $directoryManager)
    {}

    public static function create(ContainerInterface $container): static{
        return new static($container->get('developer_directory.manager'));
    }

    public function getFormId(): string
    {
        return 'developer_directory_developer_form';
    }

    #[Override]
    public function buildForm(array $form, FormStateInterface $form_state, ?NodeInterface $developer = NULL)
    {
        $form['name'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Nombre'),
            '#required' => true,
            '#maxlength' => 120,
            '#default_value' => $developer?->getTitle() ?? '',
        ];

        $form['email'] = [
            '#type' => 'email',
            '#title' => $this->t('Email'),
            '#required' => true,
            '#default_value' =>  $developer?->get('field_email')->value ?? '',
        ];

        $form['bio'] = [
            '#type' => 'textarea',
            '#title' => $this->t('Biografía'),            
            '#default_value' => $developer?->get('field_bio')->value ?? '',
        ];

        $form['experience'] = [
            '#type' => 'select',
            '#title' => $this->t('Experiencia'),
            '#options' => [                
                'junior' => $this->t('Junior'),
                'mid' => $this->t('Intermedio'),
                'senior' => $this->t('Senior')
            ],
            '#required' => true,
            '#default_value' => $developer?->get('field_experience')->value ?? 'junior',
        ];

        $form['portfolio'] = [
            '#type' => 'url',
            '#title' => $this->t('Portfolio'),            
            '#default_value' => $developer?->get('field_portfolio')->uri ?? '',
        ];

        $form['available'] = [
            '#type' => 'checkbox',
            '#title' => $this->t('Disponible para trabajar'),
            '#default_value' => (bool) $developer?->get('field_available')->value ?? false
        ];

        $form['actions'] = [
            '#type' => 'actions'
        ];

        $form['actions']['submit'] = [
            '#type' => 'submit',
            '#value' => $developer ? $this->t('Actualizar desarrollador') : $this->t('Crear desarrollador')
        ];

        if($developer !== null){
            $form_state->set('developer_id', $developer->id());
        }

        return $form;
    }

    #[Override]
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        $name = (string) $form_state->getValue('name');
        if(strlen($name) > 10){
            $form_state->setErrorByName('name', $this->t('El nombre no puede ser mayor a 10.'));
        }

        $email = trim((string) $form_state->getValue('email'));
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            $form_state->setErrorByName('email', $this->t('El email no es correcto.'));
        }
    }

    #[Override]
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $values = [
            'name' => $form_state->getValue('name'),
            'email' => $form_state->getValue('email'),
            'bio' => $form_state->getValue('bio'),
            'experience' => $form_state->getValue('experience'),
            'portfolio' => $form_state->getValue('portfolio'),
            'available' => $form_state->getValue('available'),
        ];

        $developerId = $form_state->get('developer_id');

        if($developerId !== null){
            // Modificación
            $developer = $this->directoryManager->getDeveloper($developerId);

            if($developer === null){
                throw new \Symfony\Component\Httpkernel\Exception\NotFoundHttpException();
            }

            $developer = $this->directoryManager->updateDeveloper($developer, $values);

            $this->messenger()->addStatus($this->t('Se ha actualizado el desarrollador @name.', ['@name' => $developer->getTitle()]));

        }else{
            // Inserción
            $developer = $this->directoryManager->createDeveloper($values);
            $this->messenger()->addStatus($this->t('Se ha creado el desarrollador @name.', ['@name' => $developer->getTitle()]));
        }                

        $form_state->setRedirect('developer_directory.list');
    }
}