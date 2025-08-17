<?php

// SQLite database path
$dbPath = '/var/task/database/database.sqlite';

// Check if database exists and has tables
try {
    $pdo = new PDO("sqlite:$dbPath");
    $result = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
    $tables = $result->fetchAll();

    // If no tables exist, run migrations
    if (empty($tables)) {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);

        // Seed data if needed
        if (class_exists('\Database\Seeders\DatabaseSeeder')) {
            \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
        }
    }
} catch (\Exception $e) {
    // Log error but don't fail
    error_log('SQLite bootstrap error: ' . $e->getMessage());
}
