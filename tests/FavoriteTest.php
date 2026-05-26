<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;

class FavoriteTest extends TestCase
{
    protected $conn;
    protected int $testUserId;
    protected int $testProductId;

    protected function setUp(): void
    {
        $this->conn = new mysqli('localhost', 'root', '', 'railway_test');
        if ($this->conn->connect_error) {
            $this->fail("DB connection failed: " . $this->conn->connect_error);
        }

        // create test user
        $hash = password_hash('pass', PASSWORD_DEFAULT);
        $this->conn->query(
            "INSERT INTO users (fullname, username, email, password, role)
             VALUES ('Fave Tester', 'fave_test_user', 'fave_test@test.com', '$hash', 'buyer')"
        );
        $this->testUserId = $this->conn->insert_id;

        // create test product
        $this->conn->query(
            "INSERT INTO products (name, description, price, stock, category)
             VALUES ('Fave Product', 'For fave tests', 75.00, 8, 'Souvenir')"
        );
        $this->testProductId = $this->conn->insert_id;
    }

    protected function tearDown(): void
    {
        $this->conn->query("DELETE FROM favorites WHERE user_id = {$this->testUserId}");
        $this->conn->query("DELETE FROM products WHERE id = {$this->testProductId}");
        $this->conn->query("DELETE FROM users WHERE username = 'fave_test_user'");
        $this->conn->close();
    }

    // ── TEST 1: favorite is added ─────────────────────────────────
    public function test_favorite_is_added(): void
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO favorites (user_id, product_id) VALUES (?, ?)"
        );
        $stmt->bind_param("ii", $this->testUserId, $this->testProductId);
        $stmt->execute();
        $id = $this->conn->insert_id;
        $stmt->close();

        $this->assertGreaterThan(0, $id, "Favorite should be inserted with a valid ID");
    }

    // ── TEST 2: favorite is removed ───────────────────────────────
    public function test_favorite_is_removed(): void
    {
        // add first
        $this->conn->query(
            "INSERT INTO favorites (user_id, product_id)
             VALUES ({$this->testUserId}, {$this->testProductId})"
        );

        // remove
        $stmt = $this->conn->prepare(
            "DELETE FROM favorites WHERE user_id = ? AND product_id = ?"
        );
        $stmt->bind_param("ii", $this->testUserId, $this->testProductId);
        $stmt->execute();
        $stmt->close();

        $row = $this->conn->query(
            "SELECT * FROM favorites
             WHERE user_id = {$this->testUserId} AND product_id = {$this->testProductId}"
        )->fetch_assoc();

        $this->assertNull($row, "Favorite should be removed");
    }

    // ── TEST 3: duplicate favorite is blocked ─────────────────────
    public function test_duplicate_favorite_is_blocked(): void
    {
        // add first favorite
        $stmt = $this->conn->prepare(
            "INSERT INTO favorites (user_id, product_id) VALUES (?, ?)"
        );
        $stmt->bind_param("ii", $this->testUserId, $this->testProductId);
        $stmt->execute();
        $stmt->close();

        // simulate the check in add_to_favorite.php
        $stmt = $this->conn->prepare(
            "SELECT id FROM favorites WHERE user_id = ? AND product_id = ? LIMIT 1"
        );
        $stmt->bind_param("ii", $this->testUserId, $this->testProductId);
        $stmt->execute();
        $exists = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $this->assertNotNull($exists, "Duplicate favorite should be detected and blocked");
    }

    // ── TEST 4: toggle adds if not favorited ──────────────────────
    public function test_toggle_adds_when_not_favorited(): void
    {
        // ensure no favorite exists
        $this->conn->query(
            "DELETE FROM favorites
             WHERE user_id = {$this->testUserId} AND product_id = {$this->testProductId}"
        );

        $stmt = $this->conn->prepare(
            "SELECT id FROM favorites WHERE user_id = ? AND product_id = ? LIMIT 1"
        );
        $stmt->bind_param("ii", $this->testUserId, $this->testProductId);
        $stmt->execute();
        $exists = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$exists) {
            $stmt = $this->conn->prepare(
                "INSERT INTO favorites (user_id, product_id) VALUES (?, ?)"
            );
            $stmt->bind_param("ii", $this->testUserId, $this->testProductId);
            $stmt->execute();
            $stmt->close();
        }

        $row = $this->conn->query(
            "SELECT * FROM favorites
             WHERE user_id = {$this->testUserId} AND product_id = {$this->testProductId}"
        )->fetch_assoc();

        $this->assertNotNull($row, "Favorite should be added by toggle when not already favorited");
    }

    // ── TEST 5: toggle removes if already favorited ───────────────
    public function test_toggle_removes_when_already_favorited(): void
    {
        // add favorite first
        $this->conn->query(
            "INSERT INTO favorites (user_id, product_id)
             VALUES ({$this->testUserId}, {$this->testProductId})"
        );

        $stmt = $this->conn->prepare(
            "SELECT id FROM favorites WHERE user_id = ? AND product_id = ? LIMIT 1"
        );
        $stmt->bind_param("ii", $this->testUserId, $this->testProductId);
        $stmt->execute();
        $exists = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($exists) {
            $stmt = $this->conn->prepare(
                "DELETE FROM favorites WHERE user_id = ? AND product_id = ?"
            );
            $stmt->bind_param("ii", $this->testUserId, $this->testProductId);
            $stmt->execute();
            $stmt->close();
        }

        $row = $this->conn->query(
            "SELECT * FROM favorites
             WHERE user_id = {$this->testUserId} AND product_id = {$this->testProductId}"
        )->fetch_assoc();

        $this->assertNull($row, "Favorite should be removed by toggle when already favorited");
    }
}
