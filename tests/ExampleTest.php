<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Fusion\Core\Application;
use Fusion\Core\Request;
use Fusion\Core\Response;

class ExampleTest extends TestCase
{
    private $app;

    protected function setUp(): void
    {
        $this->app = Application::getInstance();
    }

    public function testApplicationCanBeCreated()
    {
        $this->assertInstanceOf(Application::class, $this->app);
    }

    public function testRequestCanBeCreated()
    {
        $request = Request::createFromGlobals();
        $this->assertInstanceOf(Request::class, $request);
    }

    public function testResponseCanBeCreated()
    {
        $response = new Response('Hello World');
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('Hello World', $response->getContent());
    }

    public function testSecurityClass()
    {
        $this->assertTrue(\Fusion\Core\Security::validateEmail('test@example.com'));
        $this->assertFalse(\Fusion\Core\Security::validateEmail('invalid-email'));

        $password = 'password123';
        $hash = \Fusion\Core\Security::hashPassword($password);
        $this->assertTrue(\Fusion\Core\Security::verifyPassword($password, $hash));
    }
}
