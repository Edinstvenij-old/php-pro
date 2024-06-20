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
        if (empty($this->args)) {
            $this->cliHelper->error('Необходимо указать имя для миграции.');
            return;
        }

        $this->createDir();
        $this->createMigration();
    }

    protected function createDir(): void
    {
        if (!file_exists(static::MIGRATIONS_DIR)) {
            if (!mkdir(static::MIGRATIONS_DIR, 0777, true) && !is_dir(static::MIGRATIONS_DIR)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', static::MIGRATIONS_DIR));
            }
        }
    }

    protected function createMigration(): void
    {
        $name = time() . '_' . $this->args[0];
        $fullPath = static::MIGRATIONS_DIR . "/$name.sql";
        $template = $this->getMigrationTemplate();

        try {
            file_put_contents($fullPath, $template);
            $this->cliHelper->info("File was successfully created!");
            $this->cliHelper->info("File: $fullPath");
        } catch (Exception $exception) {
            $this->cliHelper->error($exception->getMessage());
        }
    }

    protected function getMigrationTemplate(): string
    {
        return <<<SQL
-- Write your migration SQL here

-- Example:
-- CREATE TABLE example (
--     id INT AUTO_INCREMENT PRIMARY KEY,
--     name VARCHAR(255) NOT NULL,
--     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
-- );

SQL;
    }
}
