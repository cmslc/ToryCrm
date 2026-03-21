<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\Validator;

class ValidatorTest extends TestCase
{
    public function testRequiredRule(): void
    {
        $v = new Validator(['name' => '']);
        $v->rule('name', 'required');
        $this->assertFalse($v->validate());
        $this->assertArrayHasKey('name', $v->errors());

        $v2 = new Validator([]);
        $v2->rule('name', 'required');
        $this->assertFalse($v2->validate());
    }

    public function testEmailRule(): void
    {
        $v = new Validator(['email' => 'not-an-email']);
        $v->rule('email', 'email');
        $this->assertFalse($v->validate());
        $this->assertArrayHasKey('email', $v->errors());

        $v2 = new Validator(['email' => 'user@example.com']);
        $v2->rule('email', 'email');
        $this->assertTrue($v2->validate());
    }

    public function testMinRule(): void
    {
        $v = new Validator(['password' => 'abc']);
        $v->rule('password', 'min:6');
        $this->assertFalse($v->validate());
        $this->assertArrayHasKey('password', $v->errors());

        $v2 = new Validator(['password' => 'abcdef']);
        $v2->rule('password', 'min:6');
        $this->assertTrue($v2->validate());
    }

    public function testMaxRule(): void
    {
        $v = new Validator(['name' => str_repeat('a', 256)]);
        $v->rule('name', 'max:255');
        $this->assertFalse($v->validate());
        $this->assertArrayHasKey('name', $v->errors());

        $v2 = new Validator(['name' => 'Short name']);
        $v2->rule('name', 'max:255');
        $this->assertTrue($v2->validate());
    }

    public function testNumericRule(): void
    {
        $v = new Validator(['amount' => 'abc']);
        $v->rule('amount', 'numeric');
        $this->assertFalse($v->validate());
        $this->assertArrayHasKey('amount', $v->errors());

        $v2 = new Validator(['amount' => '12345']);
        $v2->rule('amount', 'numeric');
        $this->assertTrue($v2->validate());

        $v3 = new Validator(['amount' => '99.50']);
        $v3->rule('amount', 'numeric');
        $this->assertTrue($v3->validate());
    }

    public function testInRule(): void
    {
        $v = new Validator(['status' => 'invalid']);
        $v->rule('status', 'in:active,inactive,pending');
        $this->assertFalse($v->validate());
        $this->assertArrayHasKey('status', $v->errors());

        $v2 = new Validator(['status' => 'active']);
        $v2->rule('status', 'in:active,inactive,pending');
        $this->assertTrue($v2->validate());
    }

    public function testMultipleRules(): void
    {
        $v = new Validator(['email' => '']);
        $v->rule('email', 'required|email');
        $this->assertFalse($v->validate());

        $errors = $v->errors();
        $this->assertArrayHasKey('email', $errors);
        // Should have at least the required error
        $this->assertNotEmpty($errors['email']);
    }

    public function testPassesValidation(): void
    {
        $v = new Validator([
            'name'  => 'Nguyen Van A',
            'email' => 'nguyenvana@example.com',
            'age'   => '30',
            'role'  => 'admin',
        ]);
        $v->rule('name', 'required|min:2|max:255');
        $v->rule('email', 'required|email');
        $v->rule('age', 'required|numeric');
        $v->rule('role', 'required|in:admin,staff,manager');

        $this->assertTrue($v->validate());
        $this->assertEmpty($v->errors());
        $this->assertNull($v->firstError());
    }
}
