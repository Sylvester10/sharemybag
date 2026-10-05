<?php

$root = dirname(__DIR__);
$migrationPath = $root . '/application/migrations/013_add_signup_resume_tokens.php';

if (!file_exists($migrationPath)) {
    fwrite(STDERR, "FAIL: Migration 013 is missing.\n");
    exit(1);
}

$migration = file_get_contents($migrationPath);

if (strpos($migration, "!\$this->hasIndex('users', 'users_signup_resume_token_idx')") === false) {
    fwrite(STDERR, "FAIL: Migration 013 must skip an existing signup resume token index.\n");
    exit(1);
}

if (strpos($migration, "if (\$this->hasIndex('users', 'users_signup_resume_token_idx'))") === false) {
    fwrite(STDERR, "FAIL: Migration 013 rollback must check that the index exists before dropping it.\n");
    exit(1);
}

echo "PASS: Migration 013 safely handles an existing signup resume token index.\n";
