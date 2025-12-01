<?php
// lib/Library.php
// NOTE: session is managed by entry scripts (index.php, dashboard.php).
// Do NOT call session_start() here to avoid session unserialize issues
// before class definitions are available.

class Book {
    public $id;
    public $title;
    public $author;
    public $available;

    public function __construct($id, $title, $author, $available = true) {
        $this->id = $id;
        $this->title = $title;
        $this->author = $author;
        $this->available = $available;
    }
}

class Library {
    private $books;
    private $borrowed;

    public function __construct() {
        // Load books from session (stored as arrays) and rebuild Book objects
        $stored = isset($_SESSION['books']) ? $_SESSION['books'] : [];
        $this->books = [];
        foreach ($stored as $id => $b) {
            if (is_array($b)) {
                $this->books[$id] = new Book($b['id'], $b['title'], $b['author'], $b['available']);
            } elseif (is_object($b) && $b instanceof Book) {
                $this->books[$id] = $b;
            }
        }

        $this->borrowed = isset($_SESSION['borrowed']) ? $_SESSION['borrowed'] : [];
        if (!isset($_SESSION['return_log'])) $_SESSION['return_log'] = [];
    }

    private function persist() {
        // Store books as arrays in session to avoid serializing objects
        $out = [];
        foreach ($this->books as $id => $b) {
            /* @var $b Book */
            $out[$id] = ['id' => $b->id, 'title' => $b->title, 'author' => $b->author, 'available' => $b->available];
        }
        $_SESSION['books'] = $out;
        $_SESSION['borrowed'] = $this->borrowed;
    }

    public function addBook($title, $author) {
        $id = time() . rand(100,999);
        // store raw title/author; escaping is done on output
        $book = new Book($id, $title, $author, true);
        $this->books[$id] = $book;
        $this->persist();
        return $id;
    }

    public function getBooks() {
        return $this->books;
    }

    public function findBook($id) {
        return isset($this->books[$id]) ? $this->books[$id] : null;
    }

    public function borrowBook($id, $user) {
        $book = $this->findBook($id);
        if (!$book) return ['ok'=>false, 'msg'=>'Buku tidak ditemukan'];
        if (!$book->available) return ['ok'=>false, 'msg'=>'Buku sedang dipinjam'];
        $book->available = false;
        $this->books[$id] = $book;
        $this->borrowed[] = ['book_id'=>$id, 'user'=>htmlspecialchars($user, ENT_QUOTES), 'date'=>date('Y-m-d H:i:s')];
        $this->persist();
        return ['ok'=>true, 'msg'=>'Berhasil meminjam'];
    }

    public function returnBook($id) {
        $book = $this->findBook($id);
        if (!$book) return ['ok'=>false, 'msg'=>'Buku tidak ditemukan'];
        if ($book->available) return ['ok'=>false, 'msg'=>'Buku tidak sedang dipinjam'];
        $book->available = true;
        $this->books[$id] = $book;
        $_SESSION['return_log'][] = ['book_id'=>$id, 'date'=>date('Y-m-d H:i:s')];
        $this->persist();
        return ['ok'=>true, 'msg'=>'Buku berhasil dikembalikan'];
    }

    public function getBorrowed() {
        return $this->borrowed;
    }

    public function getReturnLog() {
        return isset($_SESSION['return_log']) ? $_SESSION['return_log'] : [];
    }

    // helper: seed sample books (for first run)
    public function seedDefault() {
        if (!count($this->books)) {
            $this->addBook('Pemrograman Dasar', 'Budi Santoso');
            $this->addBook('Struktur Data', 'Siti Aminah');
            $this->addBook('Basis Data', 'Andi Wijaya');
        }
    }
}
