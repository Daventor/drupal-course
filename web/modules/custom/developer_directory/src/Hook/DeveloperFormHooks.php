<?php

declare(strict_types = 1);

namespace Drupal\developer_directory\Hook;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslatableMarkup;

final class DeveloperFormHooks {
    use StringTranslationTrait;

    #[Hook('form_node_developer_form_alter')]
    #[Hook('form_node_developer_edit_form_alter')]
    public function alterDeveloperNodeForm(array &$form, FormStateInterface $form_state, string $form_id): void {
        if(isset($form['field_email']['widget'][0]['value'])){
            $form['field_email']['widget'][0]['value']['#description'] = $this->t('Se guardará siempre en minúsculas.');
        }

        $form['#validate'][] = [static::class, 'validateBio'];
    }

    public static function validateBio(array &$form, FormStateInterface $form_state): void{
        $bio = trim((string) $form_state->getValue(['field_bio', 0, 'value']));

        if($bio !== '' && mb_strlen($bio) < 30){
            $form_state->setErrorByName('field_bio', new TranslatableMarkup('La biografía debe tener al menos 30 caracteres.'));
        }
    }
}