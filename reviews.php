<?php
session_start();

/* =========================
   DATABASE CONNECTION
========================= */
$conn = new mysqli("localhost", "root", "", "lgu_marketplace");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'] ?? 1;
$success_msg = "";

/* =========================
   HANDLE FORM SUBMISSIONS
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ADD REVIEW
    if (isset($_POST['submit_review'])) {

        $p_id = intval($_POST['product_id']);
        $rat  = intval($_POST['rating']);
        $com  = trim($_POST['comment']);

        $stmt = $conn->prepare("
            INSERT INTO reviews (user_id, product_id, rating, comment)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                rating = VALUES(rating),
                comment = VALUES(comment)
        ");

        $stmt->bind_param("iiis", $user_id, $p_id, $rat, $com);

        if ($stmt->execute()) {
            $success_msg = "Review saved!";
        }

        $stmt->close();
    }

    // DELETE REVIEW
    if (isset($_POST['delete_review'])) {

        $r_id = intval($_POST['review_id']);

        $stmt = $conn->prepare("DELETE FROM reviews WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $r_id, $user_id);

        if ($stmt->execute()) {
            $success_msg = "Review deleted.";
        }

        $stmt->close();
    }

    // EDIT REVIEW
    if (isset($_POST['edit_review'])) {

        $r_id = intval($_POST['review_id']);
        $rat  = intval($_POST['rating']);
        $com  = trim($_POST['comment']);

        $stmt = $conn->prepare("UPDATE reviews SET rating = ?, comment = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("isii", $rat, $com, $r_id, $user_id);

        if ($stmt->execute()) {
            $success_msg = "Review updated!";
        }

        $stmt->close();
    }
}

/* =========================
   FETCH PENDING REVIEWS
========================= */
$pending_query = "SELECT DISTINCT p.id, p.name, p.image
                  FROM order_items oi
                  JOIN orders o ON oi.order_id = o.id
                  JOIN products p ON oi.product_id = p.id
                  LEFT JOIN reviews r 
                      ON r.product_id = p.id 
                      AND r.user_id = o.user_id
                  WHERE o.user_id = ?
                  AND o.status = 'Approved'
                  AND r.id IS NULL";

$stmt = $conn->prepare($pending_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$pending_result = $stmt->get_result();
$stmt->close();

/* =========================
   FETCH REVIEW HISTORY
========================= */
$history_query = "SELECT r.*, p.name, p.image
                  FROM reviews r
                  JOIN products p ON r.product_id = p.id
                  WHERE r.user_id = ?
                  ORDER BY r.created_at DESC";

$h_stmt = $conn->prepare($history_query);
$h_stmt->bind_param("i", $user_id);
$h_stmt->execute();
$history_result = $h_stmt->get_result();
$h_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ratings & Reviews - LakbayLokal</title>
<link rel="stylesheet" href="style.css">
</head>

<body>

<header>
    <div class="logo">LakbayLokal Marketplace</div>
    <nav>
        <a href="dashboard.php">← Back to Dashboard</a>
    </nav>
</header>

<div class="review-container">

<h2>Rate Your Purchases</h2>

<?php if($success_msg): ?>
    <div class="success-msg"><?php echo htmlspecialchars($success_msg); ?></div>
<?php endif; ?>

<h3>Pending Reviews</h3>

<?php if ($pending_result->num_rows > 0): ?>
    <?php while($row = $pending_result->fetch_assoc()): ?>
        <div class="review-card">
            <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="Product">

            <div style="flex-grow:1;">
                <strong><?php echo htmlspecialchars($row['name']); ?></strong>

                <form method="POST">
                    <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">

                    <select name="rating" required>
                        <option value="5">5 Stars</option>
                        <option value="4">4 Stars</option>
                        <option value="3" selected>3 Stars</option>
                        <option value="2">2 Stars</option>
                        <option value="1">1 Star</option>
                    </select>

                    <textarea name="comment" required></textarea>

                    <button type="submit" name="submit_review" class="btn-submit">
                        Post Review
                    </button>
                </form>
            </div>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p>No pending items to rate.</p>
<?php endif; ?>

<h3>Your Review History</h3>

<?php if ($history_result->num_rows > 0): ?>
    <?php while($h_row = $history_result->fetch_assoc()): ?>

        <div class="review-card">
            <img src="uploads/<?php echo htmlspecialchars($h_row['image']); ?>" alt="Product">

            <div style="flex-grow:1;">
                <strong><?php echo htmlspecialchars($h_row['name']); ?></strong>

                <div class="star-rating">
                    <?php
                        echo str_repeat("★", (int)$h_row['rating'])
                           . str_repeat("☆", 5 - (int)$h_row['rating']);
                    ?>
                </div>

                <p>"<?php echo htmlspecialchars($h_row['comment']); ?>"</p>

                <!-- EDIT + DELETE BUTTONS -->
                <form method="POST" style="margin-top:10px;">
                    <input type="hidden" name="review_id" value="<?php echo $h_row['id']; ?>">

                    <select name="rating">
                        <?php for($i=5; $i>=1; $i--): ?>
                            <option value="<?php echo $i; ?>"
                                <?php echo ($h_row['rating'] == $i) ? 'selected' : ''; ?>>
                                <?php echo $i; ?> Stars
                            </option>
                        <?php endfor; ?>
                    </select>

                    <textarea name="comment"><?php echo htmlspecialchars($h_row['comment']); ?></textarea>

                    <button type="submit" name="edit_review" class="btn-edit">
                        Update
                    </button>

                    <button type="submit" name="delete_review" class="btn-delete"
                        onclick="return confirm('Delete this review?');">
                        Delete
                    </button>
                </form>
            </div>
        </div>

    <?php endwhile; ?>
<?php else: ?>
    <p>You haven't shared any reviews yet.</p>
<?php endif; ?>

</div>

<footer>
&copy; 2026 LakbayLokal
</footer>

</body>
</html>