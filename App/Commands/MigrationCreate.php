<?php

namespace App\Commands;

use CliHelper;
use Exception;

class MigrationCreate implements Command
{
    const MIGRATIONS_DIR = BASE_DIR . '/migrations';

    public function __construct(public CliHelper $cliHelper, public array $args = [])
    {
    }

    public function handle(): void
    {
        $this->createMigrationDirectory();
        $this->createMigrationFile();
    }

    protected function createMigrationDirectory(): void
    {
        if (!file_exists(static::MIGRATIONS_DIR)) {
            mkdir(static::MIGRATIONS_DIR);
            $this->cliHelper->info("Migrations directory created: " . static::MIGRATIONS_DIR);
        }
    }

    protected function createMigrationFile(): void
    {
        $name = uniqid() . '_' . $this->sanitizeMigrationName($this->args[0]);
        $fullPath = static::MIGRATIONS_DIR . "/$name.sql";

        try {
            file_put_contents($fullPath, '');
            $this->cliHelper->success("Migration file successfully created: $fullPath");
        } catch (Exception $exception) {
            $this->cliHelper->error("Failed to create migration file: " . $exception->getMessage());
        }
    }

    protected function sanitizeMigrationName(string $name): string
    {
        // Remove non-alphanumeric characters and spaces
        return preg_replace('/[^a-zA-Z0-9]+/', '_', $name);
    }
}
