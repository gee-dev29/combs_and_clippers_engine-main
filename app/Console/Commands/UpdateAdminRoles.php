<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;

class UpdateAdminRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:admin-role';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $admins = \App\Models\Admin::all();

        foreach ($admins as $admin) {
            $roles = json_decode(Crypt::decryptString($admin->role), true);

            // Only update if 'blogs' key doesn't already exist
            if (!array_key_exists('blogs', $roles)) {
                $roles['blogs'] = true; // or false, depending on the role type

                $admin->role = Crypt::encryptString(json_encode($roles));
                $admin->save();

                $this->info("Updated roles for: {$admin->email}");
            } else {
                $this->info("Already has 'blogs': {$admin->email}");
            }
        }

        $this->info('Admin roles updated successfully!');
    }

}