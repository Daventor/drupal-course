<?php

namespace Drupal\developer_directory\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\developer_directory\Service\DeveloperDirectoryManager;
use Override;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DeveloperRegistrationForm extends FormBase{
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
        return 'developer_directory_registration_form';
    }

    
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $form['identity'] = [
            '#type' => 'fieldset',
            '#title' => $this->t('Datos personales'),
        ];

        $form['identity']['name'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Nombre'),
            '#required' => true,
            '#maxlength' => 10,
            '#default_value' => $form_state->getValue('name', '')
        ];

        $form['identity']['email'] = [
            '#type' => 'email',
            '#title' => $this->t('Correo electrónico'),
            '#required' => true,
            '#default_value' => $form_state->getValue('email', '')
        ];

        $form['portfolio'] = [
            '#type' => 'url',
            '#title' => $this->t('Portfolio'),
            '#description' => $this->t('Introduce una url completa, por ejemplo https://example.com'),
            '#default_value' => $form_state->getValue('portfolio', '')
        ];

        $form['experience'] = [
            '#type' => 'select',
            '#title' => $this->t('Experiencia'),
            '#options' => [
                '' => $this->t('- Selecciona una opción -'),
                'junior' => $this->t('Junior'),
                'mid' => $this->t('Intermedio'),
                'senior' => $this->t('Senior')
            ],
            '#required' => true,
            '#default_value' => $form_state->getValue('experience', '')
        ];

        $form['technologies'] = [
            '#type' => 'checkboxes',
            '#title' => $this->t('Tecnologías'),
            '#options' => [
                'php' => 'PHP',
                'drupal' => 'Drupal',
                'symfony' => 'Symfony',
                'javascript' => 'Javascript',
                'other' => $this->t('Otras')
            ],
            '#default_value' => $form_state->getValue('technologies', [])
        ];

        $form['other_technology'] = [
            '#type' => 'textfield',
            '#title' => $this->t('¿Qué otras tecnologías?'),
            '#states' => [
                'visible' => [
                    ':input[name="technologies[other]"]' => ['checked' => true]
                ]
            ]
        ];

        $form['available'] = [
            '#type' => 'checkbox',
            '#title' => $this->t('Disponible para nuevos proyectos'),
            '#default_value' => $form_state->getValue('available', 0)
        ];

        $form['comments'] = [
            '#type' => 'textarea',
            '#title' => $this->t('Comentarios'),
            '#rows' => 4,
            '#maxlength' => 1000,
            '#default_value' => $form_state->getValue('comments', '')
        ];

        $result = $form_state->get('registration_result');

        // Contenedor en el que mostraremos el resultado.
        $form['result'] = [
            '#type' => 'container',
            '#attributes' => [
                'id' => 'developer-registration-result',
            ],
        ];

        if($result !== null){
            $form['result']['summary'] = [
                '#theme' => 'item_list',
                '#title' => $this->t('Datos procesados'),
                '#items' => [
                    $this->t('Nombre: @name', ['@name' => $result['name'],]),
                    $this->t('Correo: @email', ['@email' => $result['email'],]),
                    $this->t('Experiencia: @experience', ['@experience' => $result['experience'],]),
                    $this->t('Disponible: @available', [
                        '@available' => $result['available'] ? $this->t('Sí') : $this->t('No'),
                    ]),
                ],
            ];
        }

        $form['actions'] = [
            '#type' => 'actions'
        ];

        $form['actions']['submit'] = [
            '#type' => 'submit',
            '#value' => $this->t('Registrar'),
            '#ajax' => [
                'callback' => '::ajaxRefreshResult',
                'wrapper' => 'developer-registration-result'
            ]
        ];

        return $form;
    }
    
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        $name = trim((string) $form_state->getValue('name'));
        if($name === ''){
            $form_state->setErrorByName('name', $this->t('El nombre no puede estar vacío'));
        }

        $email = trim((string) $form_state->getValue('email'));
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            $form_state->setErrorByName('email', $this->t('El email no es correcto.'));
        }

        $technologies = $form_state->getValue('technologies') ?? [];
        if(!empty($technologies['other']) && trim((string) $form_state->getValue('other_technology')) === ''){
            $form_state->setErrorByName('other_technology', $this->t('Indica al menos una tecnología adicional.'));
        }

        $comments = (string) $form_state->getValue('comments');
        if(strlen($comments) > 1000){
            $form_state->setErrorByName('comments', $this->t('Los comentarios no pueden superar los 1000 caracteres.'));
        }
    }

    
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $data = [
            'name' => $form_state->getValue('name'),
            'email' => $form_state->getValue('email'),
            'portfolio' => $form_state->getValue('portfolio'),
            'experience' => $form_state->getValue('experience'),
            'technologies' => array_values(array_filter($form_state->getValue('technologies') ?? [])),            
            'available' => $form_state->getValue('available'),
            'comments' => $form_state->getValue('comments'),
        ];

        $result = $this->directoryManager->processRegistration($data);

        $form_state->set('registration_result', $result);

        $this->messenger()->addStatus($this->t('Los datos se han procesado correctamente.'));

        $form_state->setRebuild();
    }

    public function ajaxRefreshResult(array &$form, FormStateInterface $form_state,): array {
        return $form['result'];
    }
}