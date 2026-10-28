<?php

namespace Drupal\developer_directory\Form;

use Drupal\Core\Entity\ContentEntityDeleteForm;
use Drupal\Core\Url;
use Override;

class DeveloperDeleteRedirectForm extends ContentEntityDeleteForm {
    #[Override]
    protected function getRedirectUrl()
    {
        return Url::fromRoute('developer_directory.list');
    }
}