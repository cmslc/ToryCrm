<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\PermissionService;

class PermissionServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Reset session before each test
        $_SESSION = [];
        // Clear static cache via reflection
        $reflection = new \ReflectionClass(PermissionService::class);
        $cache = $reflection->getProperty('cache');
        $cache->setAccessible(true);
        $cache->setValue(null, []);
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        parent::tearDown();
    }

    public function testAdminCanDoAnything(): void
    {
        $_SESSION['user'] = [
            'id'   => 1,
            'name' => 'Admin',
            'role' => 'admin',
        ];

        $this->assertTrue(PermissionService::can('contacts', 'view'));
        $this->assertTrue(PermissionService::can('deals', 'create'));
        $this->assertTrue(PermissionService::can('settings', 'delete'));
        $this->assertTrue(PermissionService::can('anything', 'whatever'));
    }

    public function testGuestCantDoAnything(): void
    {
        // No session user
        unset($_SESSION['user']);

        $this->assertFalse(PermissionService::can('contacts', 'view'));
        $this->assertFalse(PermissionService::can('deals', 'create'));
        $this->assertFalse(PermissionService::can('settings', 'delete'));
    }
}
