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
        } catch (PDOException $exception) {
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
        $migrations = array_values(array_diff(
            $migrations,
            ['.', '..']
        ));

        // Exclude files ending with '~'
        $migrations = array_filter($migrations, function($file) {
            return substr($file, -1) !== '~';
        });

        $this->cliHelper->notice(json_encode($migrations));

        $handledMigrations = $this->getHandledMigrations();

        $this->cliHelper->notice(json_encode($handledMigrations));

        if (!empty($migrations)) {
            foreach ($migrations as $migration) {
                $this->cliHelper->notice("- run `$migration`");
                if (in_array($migration, $handledMigrations)) {
                    $this->cliHelper->notice("-- skip `$migration`");
                    continue;
                }

                $sql = file_get_contents(static::MIGRATIONS_DIR . "/$migration");

                if (empty($sql)) {
                    $this->cliHelper->error("Файл миграции пуст: $migration");
                    continue;
                }

                $this->cliHelper->notice("Содержимое файла миграции `$migration`: $sql");

                $query = db()->prepare($sql);

                if ($query->execute()) {
                    $this->createMigrationRecord($migration);
                    $this->cliHelper->success("- `$migration` migrated");
                }
            }
        }
    }

    protected function createMigrationRecord(string $migration): void
    {
        $query = db()->prepare("INSERT INTO migrations (name) VALUES (:name)");
        $query->bindParam('name', $migration);
        $query->execute();
    }

    protected function getHandledMigrations(): array
    {
        $query = db()->prepare('SELECT name FROM migrations');
        $query->execute();

        return array_map(fn($item) => $item['name'], $query->fetchAll());
    }

    protected function createMigrationTable(): void
    {
        $this->cliHelper->info('- Run migration table query');

        $query = db()->prepare("
            CREATE TABLE IF NOT EXISTS migrations (
                id INT(8) UNSIGNED PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL UNIQUE
            )"
        );

        if (!$query->execute()) {
            throw new Exception("Smth went wrong with migration table query");
        }

        $this->cliHelper->success('- Migrations table was created!');
    }
}
