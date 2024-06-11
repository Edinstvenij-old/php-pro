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
        $this->createDir();
        $this->createMigration();
    }

    protected function createDir(): void
    {
        if (!file_exists(static::MIGRATIONS_DIR)) {
            mkdir(static::MIGRATIONS_DIR);
        }
    }

    protected function createMigration(): void
    {
        $name = time() . '_' . $this->args[0];
        $fullPath = static::MIGRATIONS_DIR . "/$name.sql";

        try {
            file_put_contents($fullPath, '', FILE_APPEND);
            $this->cliHelper->info("File was successfully created!");
            $this->cliHelper->info("File: $fullPath");
        } catch (Exception $exception) {
            $this->cliHelper->error($exception->getMessage());
        }
    }
}
