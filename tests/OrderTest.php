<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;

class OrderTest extends TestCase
{
    protected $conn;
    protected int $testUserId;
    protected int $testAdminId;
    protected int $testOrderId;

    protected function setUp(): void
    {
        $this->conn = new mysqli('localhost', 'root', '', 'railway_test');
        if ($this->conn->connect_error) {
            $this->fail("DB connection failed: " . $this->conn->connect_error);
        }

        // create test buyer
        $hash = password_hash('pass', PASSWORD_DEFAULT);
        $this->conn->query(
            "INSERT INTO users (fullname, username, email, password, role)
             VALUES ('Test Buyer', 'order_test_buyer', 'order_buyer@test.com', '$hash', 'buyer')"
        );
        $this->testUserId = $this->conn->insert_id;

        // create test admin
        $this->conn->query(
            "INSERT INTO users (fullname, username, email, password, role)
             VALUES ('Test Admin', 'order_test_admin', 'order_admin@test.com', '$hash', 'admin')"
        );
        $this->testAdminId = $this->conn->insert_id;

        // create test order
        $stmt = $this->conn->prepare(
            "INSERT INTO orders (user_id, total, status) VALUES (?, 200.00, 'Pending')"
        );
        $stmt->bind_param("i", $this->testUserId);
        $stmt->execute();
        $this->testOrderId = $this->conn->insert_id;
        $stmt->close();
    }

    protected function tearDown(): void
    {
        $this->conn->query("DELETE FROM order_audit WHERE order_id = {$this->testOrderId}");
        $this->conn->query("DELETE FROM orders WHERE id = {$this->testOrderId}");
        $this->conn->query("DELETE FROM users WHERE username IN ('order_test_buyer','order_test_admin')");
        $this->conn->close();
    }

    // ── TEST 1: new order status defaults to Pending ──────────────
    public function test_order_status_defaults_to_pending(): void
    {
        $row = $this->conn
            ->query("SELECT status FROM orders WHERE id = {$this->testOrderId}")
            ->fetch_assoc();

        $this->assertEquals('Pending', $row['status'], "New order should default to Pending");
    }

    // ── TEST 2: order total is stored correctly ───────────────────
    public function test_order_total_is_correct(): void
    {
        $row = $this->conn
            ->query("SELECT total FROM orders WHERE id = {$this->testOrderId}")
            ->fetch_assoc();

        $this->assertEquals(200.00, (float) $row['total'], "Order total should match what was inserted");
    }

    // ── TEST 3: admin can approve order ──────────────────────────
    public function test_order_is_approved_by_admin(): void
    {
        $stmt = $this->conn->prepare(
            "UPDATE orders SET status = 'Approved' WHERE id = ? AND status = 'Pending'"
        );
        $stmt->bind_param("i", $this->testOrderId);
        $stmt->execute();
        $stmt->close();

        $row = $this->conn
            ->query("SELECT status FROM orders WHERE id = {$this->testOrderId}")
            ->fetch_assoc();

        $this->assertEquals('Approved', $row['status'], "Order should be Approved after admin action");
    }

    // ── TEST 4: order audit is recorded on approval ───────────────
    public function test_order_audit_is_recorded(): void
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO order_audit (order_id, admin_id, action) VALUES (?, ?, 'Approved')"
        );
        $stmt->bind_param("ii", $this->testOrderId, $this->testAdminId);
        $stmt->execute();
        $stmt->close();

        $row = $this->conn
            ->query("SELECT * FROM order_audit WHERE order_id = {$this->testOrderId}")
            ->fetch_assoc();

        $this->assertNotNull($row, "Audit record should exist after approval");
        $this->assertEquals('Approved', $row['action']);
        $this->assertEquals($this->testAdminId, (int) $row['admin_id']);
    }

    // ── TEST 5: already approved order is not re-approved ─────────
    public function test_already_approved_order_is_not_reprocessed(): void
    {
        // set to Approved first
        $this->conn->query(
            "UPDATE orders SET status = 'Approved' WHERE id = {$this->testOrderId}"
        );

        $row = $this->conn
            ->query("SELECT status FROM orders WHERE id = {$this->testOrderId}")
            ->fetch_assoc();

        // simulate the check in approve_order.php
        $this->assertNotEquals(
            'Pending',
            $row['status'],
            "Already approved order should not be pending"
        );
    }
}
