<?php

declare(strict_types = 1);

namespace Drupal\Tests\developer_directory\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Override;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

#[Group('developer_directory')]
#[RunTestsInSeparateProcesses]
final class SettingsTest extends KernelTestBase{
    // Ajusta la lista a las dependencias de tu módulo (developer_directory.info.yml)
    protected static $modules = ['system', 'user', 'node', 'developer_directory'];

    
    protected function setUp(): void
    {
        parent::setUp();
        $this->installConfig(['developer_directory']);
    }

    public function testDefaultSettings(): void {
        $config = $this->config('developer_directory.settings');
        $this->assertTrue($config->get('directory_enabled'));
        $this->assertSame(10, $config->get('items_per_page'));
    }

    public function testItemsPerPageOutOfRange(): void {
        $data = $this->config('developer_directory.settings')->getRawData();
        $data['items_per_page'] = 500;

        $violations = $this->container->get('config.typed')->createFromNameAndData('developer_directory.settings', $data)->validate();
        $this->assertCount(1, $violations);
    }
}