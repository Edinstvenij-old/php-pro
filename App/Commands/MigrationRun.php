<?php

namespace App\Commands;

use CliHelper;
use PDOException;
use splitbrain\phpcli\Exception;

class MigrationRun implements Command
{
    const MIGRATIONS_DIR = BASE_DIR . '/migrations';

    public function __construct(public CliHelper $cliHelper, public array $args = [])
    {
    }

    public function handle(): void
    {
        try {
            db()->beginTransaction();
            $this->cliHelper->info('Migration process start...');

            $this->createMigrationTable();
            $this->runMigrations();

            if (db()->inTransaction()) {
                db()->commit();
            }

            $this->cliHelper->info('Migration process is finished!');
        } catch (PDOException | Exception $exception) {
            if (db()->inTransaction()) {
                db()->rollBack();
            }
            $this->cliHelper->fatal($exception->getMessage(), $exception->getTrace());
        }
    }

    protected function runMigrations(): void
    {
        $this->cliHelper->info('');
        $this->cliHelper->info('Fetch migrations..');

        $migrations = scandir(static::MIGRATIONS_DIR);
        $migrations = array_values(array_filter($migrations, function ($file) {
            return preg_match('/^\d+_[a-zA-Z0-9_]+\.sql$/', $file) === 1;
        }));

        $handledMigrations = $this->getHandledMigrations();

        if (!empty($migrations)) {
            foreach ($migrations as $migration) {
                $this->cliHelper->notice("- run `$migration`");
                if (in_array($migration, $handledMigrations)) {
                    $this->cliHelper->notice("-- skip `$migration`");
                    continue;
                }

                $sql = file_get_contents(static::MIGRATIONS_DIR . "/$migration");

                try {
                    $query = db()->prepare($sql);
                    if ($query->execute()) {
                        $this->createMigrationRecord($migration);
                        $this->cliHelper->success("- `$migration` migrated");
                    } else {
                        $this->cliHelper->error("- `$migration` migration failed");
                    }
                } catch (PDOException $exception) {
                    // Обробляємо виняток, який виникає при спробі створення вже існуючого стовпця
                    if ($exception->errorInfo[1] === 1060) { // Перевірка на SQLSTATE для дублікату стовпця
                        $this->cliHelper->warning("- `$migration` migration skipped: Column already exists");
                        continue;
                    }
                    $this->cliHelper->error("- `$migration` migration failed: " . $exception->getMessage());
                }
            }
        }
    }

    protected function createMigrationRecord(string $migration): void
    {
        try {
            $query = db()->prepare("INSERT INTO migrations (name) VALUES (:name)");
            $query->bindParam('name', $migration);
            if ($query->execute()) {
                $this->cliHelper->info("- `$migration` migration recorded");
            } else {
                $this->cliHelper->error("- `$migration` migration record failed");
            }
        } catch (PDOException $exception) {
            $this->cliHelper->error("- `$migration` migration record failed: " . $exception->getMessage());
        }
    }

    protected function getHandledMigrations(): array
    {
        try {
            $query = db()->prepare('SELECT name FROM migrations');
            if ($query->execute()) {
                return array_map(fn($item) => $item['name'], $query->fetchAll());
            } else {
                $this->cliHelper->error("Failed to fetch handled migrations");
                return [];
            }
        } catch (PDOException $exception) {
            $this->cliHelper->error("Failed to fetch handled migrations: " . $exception->getMessage());
            return [];
        }
    }

    protected function createMigrationTable(): void
    {
        $this->cliHelper->info('- Run migration table query');

        try {
            $query = db()->prepare("
                CREATE TABLE IF NOT EXISTS migrations (
                    id INT(8) UNSIGNED PRIMARY KEY AUTO_INCREMENT,
                    name VARCHAR(255) NOT NULL UNIQUE
                )"
            );

            if ($query->execute()) {
                $this->cliHelper->success('- Migrations table was created!');
            } else {
                throw new Exception("Failed to create migrations table");
            }
        } catch (PDOException $exception) {
            throw new Exception("Failed to create migrations table: " . $exception->getMessage());
        }
    }
}
