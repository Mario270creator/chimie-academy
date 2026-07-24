<?php
require __DIR__ . '/core.php';
$demo = q_one("SELECT * FROM users WHERE username = 'elev_demo'");
if ($demo === null) {
    flash('warning', 'Contul demo nu mai există.');
    redirect('login.php');
}
session_regenerate_id(true);
$_SESSION['user_id'] = (int) $demo['id'];
flash('success', 'Ai intrat în contul demo de elev.');
redirect('dashboard.php');
