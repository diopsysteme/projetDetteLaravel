<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use App\Jobs\ProcessUserCreation;
use App\Jobs\RetryCloudinaryUploadJob;

class RetryCloudinaryUploadCommand extends Command
{
    protected $signature = 'cloudinary:retry-upload';
    protected $description = 'Retry Cloudinary photo upload for users whose photos are not hosted on Cloudinary';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Rechercher les utilisateurs dont la photo n'est pas sur Cloudinary
        $users = User::whereNotNull('photo')->where('photo', 'not like', '%cloudinary.com%')->get();

        if ($users->isEmpty()) {
            $this->info('No users found for re-upload.');
            return;
        }

        // Pour chaque utilisateur, déclencher un job pour retenter l'upload
        foreach ($users as $user) {
            ProcessUserCreation::dispatch($user);
            $this->info('Retried upload for user: ' . $user->photo);
        }

        $this->info('Cloudinary photo re-upload jobs dispatched.');
    }
}
