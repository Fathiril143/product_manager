<?php
require_once __DIR__ . "/../config/db.php";

session_start();
$_SESSION["csrf"] ??= bin2hex(random_bytes(32));

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
$token = $_POST["csrf"] ?? "";

if (!$id || !hash_equals($_SESSION["csrf"], $token)) {
    http_response_code(403);
    exit("Request tidak valid.");
}

$stmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
$stmt->execute(["id" => $id]);

header("Location: index.php?status=deleted");
exit;
