<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use function Laravel\Prompts\password;

class CreateAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:set {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set admin user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        User::where('name', $this->argument('name'))->update([
        'is_admin' => true,
    ]);
    }
}
