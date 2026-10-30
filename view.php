
<?php
include 'config.php';

$search = trim($_GET['search'] ?? '');

$message = '';

if (isset($_GET['updated'])) {
    $message = 'Record updated successfully!';
} elseif (isset($_GET['deleted'])) {
    $message = 'Record deleted successfully!';
}

$limit = 5;
$page = max(1, (int)($_GET['page'] ?? 1));

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

    <style>
        tbody tr:hover {
            background-color: #dbeafe !important;
            cursor: pointer;
        }

        th.sortable {
            cursor: pointer;
        }

        th.sortable:hover {
            background-color: #495057;
        }
    </style>
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
        <table id="entriesTable"
               class="table table-striped table-hover table-bordered bg-white">

            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th class="sortable" onclick="sortTable(1)">Name ↕</th>
                    <th class="sortable" onclick="sortTable(2)">Email ↕</th>
                    <th class="sortable" onclick="sortTable(3)">Phone ↕</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= (int)$row['id'] ?></td>

                        <td><?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') ?></td>

                        <td><?= htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8') ?></td>

                        <td><?= htmlspecialchars($row['phone'], ENT_QUOTES, 'UTF-8') ?></td>

                        <td><?= htmlspecialchars($row['created_at'], ENT_QUOTES, 'UTF-8') ?></td>

                        <td>
                            <a href="update.php?id=<?= (int)$row['id'] ?>"
                               class="btn btn-sm btn-warning mb-1">Edit</a>

                            <button type="button"
        class="btn btn-sm btn-danger mb-1"
        data-bs-toggle="modal"
        data-bs-target="#deleteModal"
        data-id="<?= (int)$row['id'] ?>">
    Delete
</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center">No entries found.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

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

<script>
function sortTable(columnIndex) {
    const table = document.getElementById("entriesTable");
    const tbody = table.tBodies[0];
    const rows = Array.from(tbody.rows);

    const direction = table.dataset.sortDirection === "asc" ? "desc" : "asc";

    rows.sort((rowA, rowB) => {
        const valueA = rowA.cells[columnIndex].textContent.trim().toLowerCase();
        const valueB = rowB.cells[columnIndex].textContent.trim().toLowerCase();

        return direction === "asc"
            ? valueA.localeCompare(valueB, undefined, { numeric: true })
            : valueB.localeCompare(valueA, undefined, { numeric: true });
    });

    rows.forEach(row => tbody.appendChild(row));

    table.dataset.sortDirection = direction;
}
</script>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1"
     aria-labelledby="deleteModalLabel" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">
                    Confirm Delete
                </h5>

                <button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                Are you sure you want to delete this record?
                This action cannot be undone.
            </div>

            <div class="modal-footer">

                <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">
                    Cancel
                </button>

                <form action="delete.php" method="GET">
                    <input type="hidden" name="id" id="deleteRecordId">

                    <button type="submit" class="btn btn-danger">
                        Yes, Delete
                    </button>
                </form>

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
const deleteModal = document.getElementById('deleteModal');

deleteModal.addEventListener('show.bs.modal', function (event) {

    const button = event.relatedTarget;
    const recordId = button.getAttribute('data-id');

    document.getElementById('deleteRecordId').value = recordId;
});
</script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
