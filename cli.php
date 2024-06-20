#!/usr/local/bin/php
<?php
const BASE_DIR = __DIR__;

require BASE_DIR . '/vendor/autoload.php';

use App\Commands\Command;
use App\Commands\MigrationCreate;
use App\Commands\MigrationRun;
use App\Commands\Seeds\FolderSeed;
use App\Commands\Seeds\NoteSeed;
use App\Commands\Seeds\UsersSeed;
use Dotenv\Dotenv;
use splitbrain\phpcli\CLI;
use splitbrain\phpcli\Options;

class CliHelper extends CLI
{
    // register options and arguments
    protected function setup(Options $options)
    {
        // Регистрация команд и аргументов
        $options->registerCommand('migration:create', 'Create migration file');
        $options->registerArgument('name', 'Migration file name', true, 'migration:create');
        $options->registerCommand('migration:run', 'Run all migration files');
        $options->registerCommand('seed:users', 'Fill test users');
        $options->registerArgument('count', 'Count of users', true, 'seed:users');
        $options->registerCommand('seed:folders', 'Fill test folders');
        $options->registerArgument('min', 'Count of min folders per user', true, 'seed:folders');
        $options->registerArgument('max', 'Count of max folders per user', true, 'seed:folders');
        $options->registerCommand('seed:notes', 'Fill test notes');
        $options->registerArgument('min', 'Count of min notes per user', true, 'seed:notes');
        $options->registerArgument('max', 'Count of max notes per user', true, 'seed:notes');

        // Загрузка переменных окружения
        $dotenv = Dotenv::createUnsafeImmutable(BASE_DIR);
        $dotenv->load();
    }

    // Реализация основного функционала
    protected function main(Options $options)
    {
        // Определение команды
        $cmd = match ($options->getCmd()) {
            "migration:create" => MigrationCreate::class,
            "migration:run" => MigrationRun::class,
            "seed:users" => UsersSeed::class,
            "seed:folders" => FolderSeed::class,
            "seed:notes" => NoteSeed::class,
            default => null
        };

        // Проверка существования класса команды
        if ($cmd && class_exists($cmd)) {
            $class = new $cmd($this, $options->getArgs());
            if ($class instanceof Command) {
                call_user_func([$class, 'handle']);
            } else {
                $this->error("Команда '$cmd' не реализует интерфейс Command.");
            }
        } else {
            $this->warning('Команда не выбрана или не существует');
            echo $options->help();
        }
    }
}

// Запуск CLI
$cli = new CliHelper();
$cli->run();
