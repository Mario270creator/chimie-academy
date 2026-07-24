<?php
require __DIR__ . '/core.php';
$me = require_admin();

$snapshot = [
    'meta' => [
        'app' => APP_NAME,
        'generatedAt' => now_iso(),
        'engine' => 'mysql+php',
    ],
    'users' => q_all('SELECT id, full_name, username, role, is_admin, created_at FROM users ORDER BY id'),
    'classes' => q_all('SELECT * FROM classes ORDER BY id'),
    'enrollments' => q_all('SELECT * FROM enrollments ORDER BY id'),
    'lessons' => q_all('SELECT * FROM lessons ORDER BY id'),
    'quizzes' => array_map(function ($q) {
        $q['questions'] = json_decode($q['questions_json'], true);
        unset($q['questions_json']);
        return $q;
    }, q_all('SELECT * FROM quizzes ORDER BY id')),
    'attempts' => q_all('SELECT * FROM attempts ORDER BY id'),
    'completions' => q_all('SELECT * FROM completions ORDER BY id'),
    'announcements' => q_all('SELECT * FROM announcements ORDER BY id'),
];

header('Content-Type: application/json; charset=utf-8');
header('Content-Disposition: attachment; filename="chimie_academy_backup_' . date('Y-m-d') . '.json"');
echo json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
