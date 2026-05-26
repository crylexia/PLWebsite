<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;

class AuthTest extends TestCase
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
        // clean up any test users
        $this->conn->query("DELETE FROM users WHERE email LIKE 'test_%@test.com'");
        $this->conn->close();
    }

    // ── helper: insert a test user ─────────────────────────────────
    private function createUser(string $username, string $email, string $password): int
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $role = 'buyer'; // add this
        $stmt = $this->conn->prepare(
            "INSERT INTO users (fullname, username, email, password, role)
            VALUES ('Test User', ?, ?, ?, ?)"
        );
        $stmt->bind_param("ssss", $username, $email, $hash, $role); // add role
        $stmt->execute();
        $id = $this->conn->insert_id;
        $stmt->close();
        return $id;
    }

    // ── TEST 1: correct password verifies successfully ─────────────
    public function test_correct_password_verifies(): void
    {
        $this->createUser('test_user1', 'test_1@test.com', 'secret123');

        $stmt = $this->conn->prepare("SELECT password FROM users WHERE username = ?");
        $u    = 'test_user1';
        $stmt->bind_param("s", $u);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $this->assertTrue(
            password_verify('secret123', $row['password']),
            "Correct password should verify successfully"
        );
    }

    // ── TEST 2: wrong password fails ──────────────────────────────
    public function test_wrong_password_fails(): void
    {
        $this->createUser('test_user2', 'test_2@test.com', 'secret123');

        $stmt = $this->conn->prepare("SELECT password FROM users WHERE username = ?");
        $u    = 'test_user2';
        $stmt->bind_param("s", $u);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $this->assertFalse(
            password_verify('wrongpassword', $row['password']),
            "Wrong password should fail verification"
        );
    }

    // ── TEST 3: duplicate username is rejected ─────────────────────
    public function test_duplicate_username_is_rejected(): void
    {
        $this->createUser('test_user3', 'test_3@test.com', 'pass123');

        $stmt = $this->conn->prepare("SELECT id FROM users WHERE username = ?");
        $u    = 'test_user3';
        $stmt->bind_param("s", $u);
        $stmt->execute();
        $stmt->store_result();
        $count = $stmt->num_rows;
        $stmt->close();

        $this->assertGreaterThan(0, $count, "Duplicate username should already exist");
    }

    // ── TEST 4: duplicate email is rejected ───────────────────────
    public function test_duplicate_email_is_rejected(): void
    {
        $this->createUser('test_user4', 'test_4@test.com', 'pass123');

        $stmt  = $this->conn->prepare("SELECT id FROM users WHERE email = ?");
        $email = 'test_4@test.com';
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $count = $stmt->num_rows;
        $stmt->close();

        $this->assertGreaterThan(0, $count, "Duplicate email should already exist in DB");
    }

    // ── TEST 5: new user role defaults to buyer ───────────────────
    public function test_new_user_role_is_buyer(): void
    {
        $this->createUser('test_user5', 'test_5@test.com', 'pass123');

        $stmt = $this->conn->prepare("SELECT role FROM users WHERE username = ?");
        $u    = 'test_user5';
        $stmt->bind_param("s", $u);
        $stmt->execute();
        $row  = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $this->assertEquals('buyer', $row['role'], "New user role should default to buyer");
    }
}
