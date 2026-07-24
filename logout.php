<?php
require __DIR__ . '/core.php';
session_destroy();
session_start();
$_SESSION['csrf_token'] = bin2hex(random_bytes(16));
flash('success', 'Ai ieșit din cont.');
redirect('index.php');
