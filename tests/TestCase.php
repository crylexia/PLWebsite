<?php

use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected $conn;

    protected function setUp(): void
    {
        $this->conn = new mysqli('localhost', 'root', '', 'railway_test');
        
        if ($this->conn->connect_error) {
            $this->fail("DB connection failed: " . $this->conn->connect_error);
        }
    }

    protected function tearDown(): void
    {
        $this->conn->close();
    }
}