<?php
include 'config.php';

$error = '';
$id = '';
$name = '';
$email = '';
$phone = '';

// Get the record ID
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    die("Invalid record ID.");
}

$id = (int) $_GET['id'];

// Fetch existing record
$stmt = $conn->prepare(
    "SELECT name, email, phone FROM entries WHERE id = ?"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($name, $email, $phone);

if (!$stmt->fetch()) {
    $stmt->close();
    die("Record not found.");
}
$stmt->close();

// Update record when form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if ($name === '' || $email === '' || $phone === '') {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (!preg_match('/^[0-9+\-\s]{7,15}$/', $phone)) {
        $error = "Please enter a valid phone number.";
    } else {
        $stmt = $conn->prepare(
            "UPDATE entries SET name = ?, email = ?, phone = ? WHERE id = ?"
        );
        $stmt->bind_param("sssi", $name, $email, $phone, $id);

        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            header("Location: view.php?updated=1");
            exit;
        } else {
            $error = "Unable to update record. Please try again.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Record - WebCraft</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow-sm mx-auto" style="max-width: 600px;">
        <div class="card-body">
            <h2 class="mb-4">Update Record</h2>

            <?php if ($error !== ''): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST"
                  action="update.php?id=<?= $id ?>"
                  novalidate>
                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" id="name" name="name"
                           class="form-control" required
                           value="<?= htmlspecialchars($name) ?>">
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email"
                           class="form-control" required
                           value="<?= htmlspecialchars($email) ?>">
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="text" id="phone" name="phone"
                           class="form-control" required
                           value="<?= htmlspecialchars($phone) ?>">
                </div>

                <button type="submit" class="btn btn-primary">
                    Save Changes
                </button>

                <a href="view.php" class="btn btn-secondary">
                    Cancel
                </a>
            </form>
        </div>
    </div>
</div>

</body>
</html>