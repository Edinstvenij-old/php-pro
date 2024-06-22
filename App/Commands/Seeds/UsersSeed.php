<?php

namespace App\Commands\Seeds;

use App\Commands\Command;
use App\Models\User;
use CliHelper;

class UsersSeed implements Command
{

    public function __construct(public CliHelper $cliHelper, public array $args = [])
    {
    }

    public function handle(): void
    {
        $count = $this->args[0];

        for($i = 0; $i < $count; $i++) {
            $data = [
                'email' => time() . "_user$i@test.com",
                'password' => password_hash('test1234', PASSWORD_BCRYPT)
            ];

            if ($user = User::create($data)) {
                $this->cliHelper->info("User with id=". $user->id . " created!");
            }
        }
    }
}