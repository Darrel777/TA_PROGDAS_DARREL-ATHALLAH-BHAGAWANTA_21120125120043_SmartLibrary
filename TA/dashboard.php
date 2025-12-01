<?php
// dashboard.php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}
require_once __DIR__ . '/lib/Library.php';
$lib = new Library();
$lib->seedDefault();

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_book'])) {
        $title = trim($_POST['title'] ?? '');
        $author = trim($_POST['author'] ?? '');
        if ($title === '' || $author === '') {
            $msg = 'Judul dan Penulis wajib diisi.';
        } else {
            // allow only admin to add books
            if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
                $lib->addBook($title, $author);
                $msg = 'Buku berhasil ditambahkan.';
            } else {
                $msg = 'Anda tidak diizinkan menambahkan buku.';
            }
        }
    } elseif (isset($_POST['borrow'])) {
        $book_id = $_POST['book_id'] ?? '';
        $user = trim($_POST['borrower'] ?? '');
        if ($user === '') {
            $msg = 'Nama peminjam wajib diisi.';
        } else {
            $r = $lib->borrowBook($book_id, $user);
            $msg = $r['msg'];
        }
    } elseif (isset($_POST['return'])) {
        $book_id = $_POST['ret_book_id'] ?? '';
        $r = $lib->returnBook($book_id);
        $msg = $r['msg'];
    }
}

$books = $lib->getBooks();
$borrowed = $lib->getBorrowed();
$returnLog = $lib->getReturnLog();
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>SmartLibrary - Dashboard</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<header class="topbar">
    <div class="brand">SmartLibrary</div>
    <div class="user">Logged in as <?=htmlspecialchars($_SESSION['user'])?> (<?=htmlspecialchars($_SESSION['role'] ?? '')?>) &nbsp;|&nbsp; <a href="logout.php" class="link">Logout</a></div>
</header>

<div class="container">
    <aside class="panel">
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <h3>Tambah Buku</h3>
        <?php if($msg): ?><div class="info"><?=$msg?></div><?php endif; ?>
        <form method="post" class="form-inline">
            <label>Judul</label>
            <input name="title" type="text" required>
            <label>Penulis</label>
            <input name="author" type="text" required>
            <button class="btn" name="add_book" type="submit">Tambah Buku</button>
        </form>
        <?php else: ?>
        <h3>Tambah Buku</h3>
        <div class="info">Hanya admin yang dapat menambah buku.</div>
        <?php endif; ?>

        <h3>Daftar Buku</h3>
        <div class="scroll">
        <table class="table">
            <thead><tr><th>Judul</th><th>Penulis</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php if(empty($books)): ?>
                <tr><td colspan="4">Belum ada buku.</td></tr>
            <?php else: foreach($books as $b): ?>
                <tr>
                    <td><?=htmlspecialchars($b->title)?></td>
                    <td><?=htmlspecialchars($b->author)?></td>
                    <td><?= $b->available ? '<span class="pill ok">Tersedia</span>' : '<span class="pill nok">Dipinjam</span>' ?></td>
                    <td>
                        <?php if($b->available): ?>
                        <form method="post" class="inline">
                            <input type="hidden" name="book_id" value="<?=$b->id?>">
                            <input type="text" name="borrower" placeholder="Nama peminjam" required>
                            <button name="borrow" type="submit" class="btn small">Pinjam</button>
                        </form>
                        <?php else: ?>
                        <form method="post" class="inline">
                            <input type="hidden" name="ret_book_id" value="<?=$b->id?>">
                            <button name="return" type="submit" class="btn small invert">Kembalikan</button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
        </div>
    </aside>

    <main class="panel">
        <h3>Riwayat Peminjaman</h3>
        <?php if(empty($borrowed)): ?>
            <p>Tidak ada peminjaman.</p>
        <?php else: ?>
            <table class="table">
                <thead><tr><th>Nama</th><th>Buku</th><th>Tanggal</th></tr></thead>
                <tbody>
                <?php foreach($borrowed as $br):
                    $book = $lib->findBook($br['book_id']);
                ?>
                    <tr>
                        <td><?=htmlspecialchars($br['user'])?></td>
                        <td><?= $book ? htmlspecialchars($book->title) : '—' ?></td>
                        <td><?=htmlspecialchars($br['date'])?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <h3>Riwayat Pengembalian</h3>
        <?php if(empty($returnLog)): ?>
            <p>Belum ada pengembalian.</p>
        <?php else: ?>
            <ul class="list-log">
            <?php foreach($returnLog as $rl):
                $book = $lib->findBook($rl['book_id']);
            ?>
                <li><?= $book ? htmlspecialchars($book->title) : '—' ?> <span class="muted">— <?=htmlspecialchars($rl['date'])?></span></li>
            <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </main>
</div>

<footer class="foot">SmartLibrary &copy; <?=date('Y')?> - Demo Praktikum</footer>
</body>
</html>
