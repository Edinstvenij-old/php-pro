<?php

namespace App\Commands;

use CliHelper;
use Exception;

class MigrationCreate implements Command
{
    const MIGRATIONS_DIR = BASE_DIR . DIRECTORY_SEPARATOR . 'migrations';

    public function __construct(public CliHelper $cliHelper, public array $args = [])
    {
    }

    public function handle(): void
    {
        if (empty($this->args)) {
            $this->cliHelper->error('Migration name argument is required.');
            return;
        }

        $this->createDir();
        $this->createMigration();
    }

    protected function createDir(): void
    {
        if (!file_exists(static::MIGRATIONS_DIR)) {
            if (!mkdir(static::MIGRATIONS_DIR, 0777, true) && !is_dir(static::MIGRATIONS_DIR)) {
                throw new Exception(sprintf('Directory "%s" was not created', static::MIGRATIONS_DIR));
            }
            $this->cliHelper->info('Migrations directory was successfully created.');
        }
    }

    protected function createMigration(): void
    {
        $name = time() . '_' . $this->args[0];
        $fullPath = static::MIGRATIONS_DIR . DIRECTORY_SEPARATOR . "$name.sql";

        try {
            file_put_contents($fullPath, '', FILE_APPEND);
            $this->cliHelper->info("File was successfully created!");
            $this->cliHelper->info("File: $fullPath");
        } catch (Exception $exception) {
            $this->cliHelper->error("Error creating migration file: " . $exception->getMessage());
        }
    }
}
