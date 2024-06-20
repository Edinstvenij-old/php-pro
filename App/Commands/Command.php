<?php

namespace App\Commands;

use CliHelper;

interface Command
{
   public function __construct(CliHelper $cliHelper, array $args = []);

   public function handle(): void;
}
