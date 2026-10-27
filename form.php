
<?php
include 'config.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if ($name === '' || $email === '' || $phone === '') {
        $message = 'All fields are required!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email!';
    } elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
        $message = 'Phone number must contain 10 digits!';
    } else {
        $stmt = $conn->prepare(
            'INSERT INTO entries (name, email, phone) VALUES (?, ?, ?)'
        );
        $stmt->bind_param('sss', $name, $email, $phone);

        if ($stmt->execute()) {
            $message = 'Entry added successfully!';
        } else {
            $message = 'Unable to save entry. Please try again.';
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Form - WebCraft</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card shadow-sm p-4 mx-auto" style="max-width: 550px;">
            <h2 class="mb-4">Add Entry</h2>

            <?php if ($message !== ''): ?>
                <div class="alert alert-info">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="form.php">
                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" id="name" name="name"
                           class="form-control" placeholder="Enter Name" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email"
                           class="form-control" placeholder="Enter Email" required>
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="tel" id="phone" name="phone"
                           class="form-control" placeholder="10-digit phone number"
                           pattern="[0-9]{10}" maxlength="10" required>
                </div>

                <button type="submit" class="btn btn-success">Submit</button>
                <button type="reset" class="btn btn-secondary">Clear Form</button>
            </form>
        </div>
    </div>
</body>
</html>
