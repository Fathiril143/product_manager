<?php
require_once __DIR__ . "/../config/db.php";
session_start();
$_SESSION["csrf"] ??= bin2hex(random_bytes(32));

$stmt = $pdo->query(
    "SELECT id, name, category, price, stock
     FROM products ORDER BY id DESC"
);
$products = $stmt->fetchAll();

$status = $_GET["status"] ?? "";
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Manager</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<main class="container">
    <div class="header">
        <h1>Product Manager</h1>
        <a class="button" href="create.php">+ Tambah Produk</a>
    </div>

    <?php if ($status === "created"): ?>
        <div class="success">Produk berhasil disimpan.</div>
    <?php elseif ($status === "updated"): ?>
        <div class="success">Produk berhasil diubah.</div>
    <?php elseif ($status === "deleted"): ?>
        <div class="success">Produk berhasil dihapus.</div>
    <?php endif; ?>

    <section class="products">
        <?php foreach ($products as $p): ?>
            <article class="card">
                <h2><?= htmlspecialchars($p["name"], ENT_QUOTES, "UTF-8") ?></h2>
                <p class="category"><?= htmlspecialchars($p["category"], ENT_QUOTES, "UTF-8") ?></p>
                <p class="price">Rp <?= number_format($p["price"], 0, ",", ".") ?></p>
                <p>Stok: <?= (int)$p["stock"] ?></p>

                <div class="actions">
                    <a class="button secondary" href="edit.php?id=<?= (int)$p["id"] ?>">Edit</a>
                    <form method="POST" action="delete.php">
                        <input type="hidden" name="id" value="<?= (int)$p["id"] ?>">
                        <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION["csrf"], ENT_QUOTES, "UTF-8") ?>">
                        <button class="button danger" type="submit"
                                onclick="return confirm('Hapus produk ini?')">
                            Hapus
                        </button>
                    </form>
                </div>
            </article>
        <?php endforeach; ?>
    </section>
</main>
</body>
</html>
