<?php

namespace App\Commands\Seeds;

use App\Commands\Command;
use App\Models\Folder;
use App\Models\User;
use CliHelper;

class FolderSeed implements Command
{

    public function __construct(public CliHelper $cliHelper, public array $args = [])
    {
    }

    public function handle(): void
    {
        $min = (int) $this->args[0];
        $max = (int) $this->args[1];

        if ($max < $min) {
            $this->cliHelper->error('Max value should be more than min value');
        }

        $users = User::all();

        foreach($users as $user) {
            $count = rand($min, $max);

            for($i = 0; $i < $count; $i++) {
                $data = [
                    'title' => "User " . $user->id . ' - ' . time() . $count,
                    'user_id' => $user->id
                ];

                if ($folder = Folder::create($data)) {
                    $this->cliHelper->info("Folder with id=". $folder->id . " created!");
                }
            }
        }
    }
}