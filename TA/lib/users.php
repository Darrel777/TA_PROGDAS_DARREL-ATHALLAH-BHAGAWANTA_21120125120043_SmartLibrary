<?php
// lib/users.php
// Simple PHP-only user store for demo purposes.
// Two accounts: admin and mahasiswa. Passwords are generated with password_hash()

$USERS = [
    'admin' => [
        // default password: admin123
        'password' => password_hash('admin123', PASSWORD_DEFAULT),
        'role' => 'admin',
        'name' => 'Administrator'
    ],

    'mahasiswa' => [
        // default password: mhs123
        'password' => password_hash('mhs123', PASSWORD_DEFAULT),
        'role' => 'mahasiswa',
        'name' => 'Mahasiswa Demo'
    ],
];

?>