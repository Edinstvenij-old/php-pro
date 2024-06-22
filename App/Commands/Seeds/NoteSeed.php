<?php

namespace App\Commands\Seeds;

use App\Commands\Command;
use App\Enums\DB\SQL;
use App\Models\Folder;
use App\Models\Note;
use App\Models\User;
use CliHelper;

class NoteSeed implements Command
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
            $this->cliHelper->info("User ID: ". $user->id);
            $count = rand($min, $max);

            $folders = Folder::where('user_id', SQL::EQUAL, $user->id)
                ->beginCondition()
                ->or('user_id', SQL::IS)
                ->and('title', SQL::EQUAL, Folder::GENERAL_FOLDER)
                ->endCondition()
                ->orderBy([
                    'user_id' => 'ASC',
                    'title' => 'ASC'
                ])
                ->get();

            foreach($folders as $folder) {
                if (rand(0, 1) === 1) {
                    continue;
                }

                for($i = 0; $i < $count; $i++) {
                    $this->cliHelper->info("Folder ID: ". $folder->id);

                    $data = [
                        'title' => 'Note - ' . time() . $count,
                        'content' => "User id " . $user->id,
                        'user_id' => $user->id,
                        'folder_id' => $folder->id,
                        'pinned' => rand(0, 1),
                        'completed' => rand(0, 1),
                    ];

                    if ($note = Note::create($data)) {
                        $this->cliHelper->info("Note with id=". $note->id . " created!");
                    }
                }
            }
        }
    }
}