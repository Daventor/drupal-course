<?php

namespace Drupal\course_demo\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Controlador principal de nuestro módulo
 */
class CourseDemoController extends ControllerBase {
    /**
     * Página de bienvenida
     */
    public function hello(string $name): array {
        return [
            '#markup' => $this->t('Hello @name', ['@name' => $name]),
        ];
    }

    /**
     * Controlador de la configuración de admin
     */
    public function admin(): array{
        return [
            '#markup' => $this->t('Página de administración de Course Demo'),
        ];
    }
}