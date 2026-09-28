<?php
require_once __DIR__ . "/../config/db.php";

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
if (!$id) {
    http_response_code(400);
    exit("ID tidak valid.");
}

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
$stmt->execute(["id" => $id]);
$product = $stmt->fetch();

if (!$product) {
    http_response_code(404);
    exit("Produk tidak ditemukan.");
}

$name = $product["name"];
$category = $product["category"];
$price = $product["price"];
$stock = $product["stock"];
$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $category = trim($_POST["category"] ?? "Umum");
    $price = filter_input(INPUT_POST, "price", FILTER_VALIDATE_FLOAT);
    $stock = filter_input(INPUT_POST, "stock", FILTER_VALIDATE_INT);

    if (mb_strlen($name) < 3) $errors["name"] = "Nama minimal 3 karakter.";
    if ($price === false || $price <= 0) $errors["price"] = "Harga harus > 0.";
    if ($stock === false || $stock < 0) $errors["stock"] = "Stok tidak boleh negatif.";

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare(
                "UPDATE products
                 SET name = :name, category = :category, price = :price, stock = :stock
                 WHERE id = :id"
            );
            $stmt->execute([
                "name" => $name,
                "category" => $category ?: "Umum",
                "price" => $price,
                "stock" => $stock,
                "id" => $id
            ]);

            header("Location: index.php?status=updated");
            exit;
        } catch (PDOException $e) {
            $errors["name"] = "Nama produk sudah digunakan.";
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<main class="container small">
    <h1>Edit Produk</h1>

    <form class="form-card" method="POST">
        <label for="name">Nama produk</label>
        <input id="name" name="name" value="<?= htmlspecialchars($name, ENT_QUOTES, "UTF-8") ?>" required minlength="3">
        <?php if (isset($errors["name"])): ?><p class="error"><?= htmlspecialchars($errors["name"]) ?></p><?php endif; ?>

        <label for="category">Kategori</label>
        <input id="category" name="category" value="<?= htmlspecialchars($category, ENT_QUOTES, "UTF-8") ?>">

        <label for="price">Harga</label>
        <input id="price" name="price" type="number" min="1" step="0.01"
               value="<?= htmlspecialchars((string)$price, ENT_QUOTES, "UTF-8") ?>" required>

        <label for="stock">Stok</label>
        <input id="stock" name="stock" type="number" min="0"
               value="<?= htmlspecialchars((string)$stock, ENT_QUOTES, "UTF-8") ?>" required>

        <div class="actions">
            <button class="button" type="submit">Simpan Perubahan</button>
            <a class="button secondary" href="index.php">Kembali</a>
        </div>
    </form>
</main>
</body>
</html>
