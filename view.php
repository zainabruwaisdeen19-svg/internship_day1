
<?php
include 'config.php';

$search = trim($_GET['search'] ?? '');

// Show message after update or delete
$message = '';
if (isset($_GET['updated'])) {
    $message = 'Record updated successfully!';
} elseif (isset($_GET['deleted'])) {
    $message = 'Record deleted successfully!';
}

// Pagination settings
$limit = 5;
$page = max(1, (int)($_GET['page'] ?? 1));

// Count matching entries
$countSql = "SELECT COUNT(*) AS total
             FROM entries
             WHERE name LIKE ? OR email LIKE ?";

$countStmt = $conn->prepare($countSql);
$searchTerm = '%' . $search . '%';
$countStmt->bind_param('ss', $searchTerm, $searchTerm);
$countStmt->execute();

$countResult = $countStmt->get_result();
$totalEntries = (int)$countResult->fetch_assoc()['total'];
$countStmt->close();

$totalPages = max(1, (int)ceil($totalEntries / $limit));
$page = min($page, $totalPages);
$offset = ($page - 1) * $limit;

// Fetch entries for the current page
$sql = "SELECT id, name, email, phone, created_at
        FROM entries
        WHERE name LIKE ? OR email LIKE ?
        ORDER BY id DESC
        LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param('ssii', $searchTerm, $searchTerm, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>WebCraft - All Entries</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>

<body class="bg-light">
<div class="container py-5">

    <h2 class="mb-4">All Entries</h2>

    <?php if ($message !== ''): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <form method="GET" action="view.php" class="mb-4">
        <div class="input-group">
            <input type="text" name="search" class="form-control"
                   placeholder="Search by name or email..."
                   value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">

            <button type="submit" class="btn btn-primary">Search</button>
            <a href="view.php" class="btn btn-secondary">Clear</a>
        </div>
    </form>

    <p class="text-muted">
        Total matching entries: <?= $totalEntries ?>
    </p>

    <div class="table-responsive">
        <table class="table table-striped table-hover table-bordered bg-white">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars((string)$row['id']) ?></td>
                        <td><?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($row['phone'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($row['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <a href="update.php?id=<?= (int)$row['id'] ?>"
                               class="btn btn-sm btn-warning mb-1">
                                Edit
                            </a>

                            <a href="delete.php?id=<?= (int)$row['id'] ?>"
                               class="btn btn-sm btn-danger mb-1"
                               onclick="return confirm('Are you sure you want to delete this record?');">
                                Delete
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center">
                        No entries found.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination buttons -->
    <?php if ($totalEntries > 0 && $totalPages > 1): ?>
        <nav aria-label="Entries pagination">
            <ul class="pagination justify-content-center">

                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link"
                       href="?search=<?= urlencode($search) ?>&page=<?= $page - 1 ?>">
                        Previous
                    </a>
                </li>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= ($i === $page) ? 'active' : '' ?>">
                        <a class="page-link"
                           href="?search=<?= urlencode($search) ?>&page=<?= $i ?>">
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                    <a class="page-link"
                       href="?search=<?= urlencode($search) ?>&page=<?= $page + 1 ?>">
                        Next
                    </a>
                </li>

            </ul>
        </nav>
    <?php endif; ?>

    <a href="form.php" class="btn btn-success">Add New Entry</a>

</div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
