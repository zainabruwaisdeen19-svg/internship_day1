<?php
include 'config.php';

// Check whether a valid ID was provided
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    header("Location: view.php");
    exit;
}

$id = (int) $_GET['id'];

// Delete the selected record safely
$stmt = $conn->prepare("DELETE FROM entries WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();

    header("Location: view.php?deleted=1");
    exit;
} else {
    echo "Error deleting record. Please try again.";
}

$stmt->close();
$conn->close();
?>