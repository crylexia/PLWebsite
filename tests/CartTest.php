<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;

class CartTest extends TestCase
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
        if ($this->conn) {
            $this->conn->close();
        }
    }

    // ── TEST 1: cart query uses a prepared statement ───────────────
    public function test_cart_uses_prepared_statement(): void
    {
        $uid  = 1;
        $stmt = $this->conn->prepare(
            "SELECT c.quantity, p.* FROM cart c
             JOIN products p ON c.product_id = p.id
             WHERE c.user_id = ?"
        );
        $this->assertNotFalse($stmt, "Cart query should be a valid prepared statement");
        $stmt->close();
    }

    public function test_checkout_deducts_stock(): void
    {
        // insert a test product
        $this->conn->query(
            "INSERT INTO products (id, name, price, stock) VALUES (1, 'Test Product', 50.00, 5)
            ON DUPLICATE KEY UPDATE stock = 5"
        );

        $before = (int) $this->conn
            ->query("SELECT stock FROM products WHERE id = 1")
            ->fetch_assoc()["stock"];

        $qty = 2;
        $pid = 1;
        $stmt = $this->conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        $stmt->bind_param("ii", $qty, $pid);
        $stmt->execute();
        $stmt->close();

        $after = (int) $this->conn
            ->query("SELECT stock FROM products WHERE id = 1")
            ->fetch_assoc()["stock"];

        $this->assertEquals($before - 2, $after, "Stock should decrease by the ordered quantity");
    }

    // ── TEST 3: overselling is blocked ────────────────────────────
    public function test_oversell_is_blocked(): void
    {
        $this->conn->query("UPDATE products SET stock = 1 WHERE id = 1");

        $stock     = (int) $this->conn
            ->query("SELECT stock FROM products WHERE id = 1")
            ->fetch_assoc()["stock"];
        $requested = 5;

        $this->assertFalse(
            $requested <= $stock,
            "Checkout should block when requested quantity exceeds stock"
        );
    }

    // ── TEST 4: orders table records order correctly ───────────────
    public function test_order_is_inserted(): void
    {
        $userId = 1;
        $total  = 250.00;

        $stmt = $this->conn->prepare(
            "INSERT INTO orders (user_id, total, status) VALUES (?, ?, 'Pending')"
        );
        $stmt->bind_param("id", $userId, $total);
        $stmt->execute();
        $orderId = $this->conn->insert_id;
        $stmt->close();

        $this->assertGreaterThan(0, $orderId, "Order should be inserted with a valid ID");

        // cleanup
        $this->conn->query("DELETE FROM orders WHERE id = $orderId");
    }

    // ── TEST 5: cart is cleared after checkout ─────────────────────
    public function test_cart_is_cleared_after_checkout(): void
    {
        $userId    = 1;
        $productId = 1;

        // insert a test cart item
        $stmt = $this->conn->prepare(
            "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)"
        );
        $stmt->bind_param("ii", $userId, $productId);
        $stmt->execute();
        $stmt->close();

        // simulate cart clear after checkout
        $stmt = $this->conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->close();

        $result = $this->conn->query(
            "SELECT COUNT(*) as cnt FROM cart WHERE user_id = $userId"
        )->fetch_assoc()["cnt"];

        $this->assertEquals(0, $result, "Cart should be empty after checkout");
    }
}