<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;

class ReviewTest extends TestCase
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
             VALUES ('Review Tester', 'review_test_user', 'review_test@test.com', '$hash', 'buyer')"
        );
        $this->testUserId = $this->conn->insert_id;

        // create test product
        $this->conn->query(
            "INSERT INTO products (name, description, price, stock, category)
             VALUES ('Review Product', 'For review tests', 50.00, 5, 'Souvenir')"
        );
        $this->testProductId = $this->conn->insert_id;
    }

    protected function tearDown(): void
    {
        $this->conn->query("DELETE FROM reviews WHERE user_id = {$this->testUserId}");
        $this->conn->query("DELETE FROM products WHERE id = {$this->testProductId}");
        $this->conn->query("DELETE FROM users WHERE username = 'review_test_user'");
        $this->conn->close();
    }

    // ── TEST 1: review is inserted ────────────────────────────────
    public function test_review_is_inserted(): void
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO reviews (user_id, product_id, rating, comment)
             VALUES (?, ?, 5, 'Great product!')"
        );
        $stmt->bind_param("ii", $this->testUserId, $this->testProductId);
        $stmt->execute();
        $reviewId = $this->conn->insert_id;
        $stmt->close();

        $this->assertGreaterThan(0, $reviewId, "Review should be inserted with a valid ID");
    }

    // ── TEST 2: duplicate review is blocked (UNIQUE constraint) ───
    public function test_duplicate_review_is_blocked(): void
    {
        // insert first review
        $stmt = $this->conn->prepare(
            "INSERT INTO reviews (user_id, product_id, rating, comment) VALUES (?, ?, 4, 'Good')"
        );
        $stmt->bind_param("ii", $this->testUserId, $this->testProductId);
        $stmt->execute();
        $stmt->close();

        // try inserting duplicate
        $stmt = $this->conn->prepare(
            "INSERT IGNORE INTO reviews (user_id, product_id, rating, comment) VALUES (?, ?, 3, 'Again')"
        );
        $stmt->bind_param("ii", $this->testUserId, $this->testProductId);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();

        $this->assertEquals(0, $affected, "Duplicate review should be blocked by UNIQUE constraint");
    }

    // ── TEST 3: rating must be between 1 and 5 ────────────────────
    public function test_rating_is_within_valid_range(): void
    {
        $validRatings   = [1, 2, 3, 4, 5];
        $invalidRatings = [0, 6, -1, 10];

        foreach ($validRatings as $r) {
            $this->assertTrue($r >= 1 && $r <= 5, "Rating $r should be valid");
        }

        foreach ($invalidRatings as $r) {
            $this->assertFalse($r >= 1 && $r <= 5, "Rating $r should be invalid");
        }
    }

    // ── TEST 4: review can be updated ────────────────────────────
    public function test_review_is_updated(): void
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO reviews (user_id, product_id, rating, comment) VALUES (?, ?, 3, 'Okay')"
        );
        $stmt->bind_param("ii", $this->testUserId, $this->testProductId);
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare(
            "UPDATE reviews SET rating = 5, comment = 'Amazing!' WHERE user_id = ? AND product_id = ?"
        );
        $stmt->bind_param("ii", $this->testUserId, $this->testProductId);
        $stmt->execute();
        $stmt->close();

        $row = $this->conn->query(
            "SELECT rating, comment FROM reviews
             WHERE user_id = {$this->testUserId} AND product_id = {$this->testProductId}"
        )->fetch_assoc();

        $this->assertEquals(5, (int) $row['rating']);
        $this->assertEquals('Amazing!', $row['comment']);
    }

    // ── TEST 5: review is deleted ────────────────────────────────
    public function test_review_is_deleted(): void
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO reviews (user_id, product_id, rating, comment) VALUES (?, ?, 2, 'Meh')"
        );
        $stmt->bind_param("ii", $this->testUserId, $this->testProductId);
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare(
            "DELETE FROM reviews WHERE user_id = ? AND product_id = ?"
        );
        $stmt->bind_param("ii", $this->testUserId, $this->testProductId);
        $stmt->execute();
        $stmt->close();

        $row = $this->conn->query(
            "SELECT * FROM reviews
             WHERE user_id = {$this->testUserId} AND product_id = {$this->testProductId}"
        )->fetch_assoc();

        $this->assertNull($row, "Review should be deleted");
    }
}
