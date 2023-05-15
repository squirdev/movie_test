<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;


    protected function setUp(): void
    {
        parent::setUp();

        // Additional setup if needed
    }

    protected function tearDown(): void
    {
        // Additional teardown if needed

        parent::tearDown();
    }

}
