<?php

declare(strict_types = 1);

namespace Drupal\developer_directory\Hook;

use Drupal\Core\Hook\Attribute\Hook;

final class ThemeHooks {

    #[Hook('theme')]
    public function theme(): array {
        return [
            'developer_profile' => [
            'variables' => [
                'name' => NULL,
                'bio' => NULL,
                'experience' => NULL,
                'skills' => NULL,
                'email' => NULL,
                'available' => FALSE,
                'portfolio_uri' => NULL,
                'portfolio_title' => NULL,
            ],
            'template' => 'developer-profile',
            ],
            'developer_directory_featured' => [
            'variables' => [
                'developers' => [],
                'title' => NULL,
            ],
            'template' => 'developer-directory-featured',
            ],
        ];
    }
}