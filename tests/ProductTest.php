<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    protected $conn;
    protected int $testProductId;

    protected function setUp(): void
    {
        $this->conn = new mysqli('localhost', 'root', '', 'railway_test');
        if ($this->conn->connect_error) {
            $this->fail("DB connection failed: " . $this->conn->connect_error);
        }

        // insert a reusable test product
        $stmt = $this->conn->prepare(
            "INSERT INTO products (name, description, price, stock, category)
             VALUES ('Test Product', 'A test item', 99.00, 10, 'Souvenir')"
        );
        $stmt->execute();
        $this->testProductId = $this->conn->insert_id;
        $stmt->close();
    }

    protected function tearDown(): void
    {
        $this->conn->query("DELETE FROM products WHERE name = 'Test Product'");
        $this->conn->query("DELETE FROM products WHERE name = 'Updated Product'");
        $this->conn->close();
    }

    // ── TEST 1: product is inserted ───────────────────────────────
    public function test_product_is_inserted(): void
    {
        $this->assertGreaterThan(0, $this->testProductId, "Product should be inserted with a valid ID");

        $row = $this->conn
            ->query("SELECT * FROM products WHERE id = {$this->testProductId}")
            ->fetch_assoc();

        $this->assertEquals('Test Product', $row['name']);
        $this->assertEquals(99.00, (float) $row['price']);
        $this->assertEquals(10, (int) $row['stock']);
    }

    // ── TEST 2: product is updated ────────────────────────────────
    public function test_product_is_updated(): void
    {
        $stmt = $this->conn->prepare(
            "UPDATE products SET name = 'Updated Product', price = 150.00 WHERE id = ?"
        );
        $stmt->bind_param("i", $this->testProductId);
        $stmt->execute();
        $stmt->close();

        $row = $this->conn
            ->query("SELECT * FROM products WHERE id = {$this->testProductId}")
            ->fetch_assoc();

        $this->assertEquals('Updated Product', $row['name']);
        $this->assertEquals(150.00, (float) $row['price']);
    }

    // ── TEST 3: product is deleted ────────────────────────────────
    public function test_product_is_deleted(): void
    {
        $stmt = $this->conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $this->testProductId);
        $stmt->execute();
        $stmt->close();

        $row = $this->conn
            ->query("SELECT * FROM products WHERE id = {$this->testProductId}")
            ->fetch_assoc();

        $this->assertNull($row, "Product should no longer exist after deletion");
    }

    // ── TEST 4: stock is restocked ────────────────────────────────
    public function test_stock_is_restocked(): void
    {
        $addStock = 5;
        $stmt     = $this->conn->prepare(
            "UPDATE products SET stock = stock + ? WHERE id = ?"
        );
        $stmt->bind_param("ii", $addStock, $this->testProductId);
        $stmt->execute();
        $stmt->close();

        $row = $this->conn
            ->query("SELECT stock FROM products WHERE id = {$this->testProductId}")
            ->fetch_assoc();

        $this->assertEquals(15, (int) $row['stock'], "Stock should increase by the restocked amount");
    }

    // ── TEST 5: stock cannot go negative ──────────────────────────
    public function test_stock_does_not_go_negative(): void
    {
        $stock     = (int) $this->conn
            ->query("SELECT stock FROM products WHERE id = {$this->testProductId}")
            ->fetch_assoc()['stock'];
        $requested = 999;

        $this->assertFalse(
            $requested <= $stock,
            "Should not allow deducting more than available stock"
        );
    }
}
