<?php

namespace App\Console\Commands;

use App\Models\Admin;
use Illuminate\Console\Command;

class InviteAdmin extends Command
{
    protected $signature = 'admin:invite {email} {--name=} {--role=super_admin}';
    protected $description = 'Invite or update an approved Google account for the administration portal';

    public function handle(): int
    {
        $email = strtolower(trim((string) $this->argument('email')));
        $role = (string) $this->option('role');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Please provide a valid Google account email.');
            return self::FAILURE;
        }
        if (!in_array($role, ['super_admin', 'admissions_staff', 'exam_manager', 'viewer'], true)) {
            $this->error('Invalid role.');
            return self::FAILURE;
        }
        $admin = Admin::firstOrNew(['email' => $email]);
        $admin->name = $this->option('name') ?: ($admin->name ?: explode('@', $email)[0]);
        $admin->role = $role;
        if (!$admin->exists) {
            $admin->status = 'pending';
        }
        $admin->save();
        $this->info("Admin invitation ready for {$admin->email} ({$admin->role}).");
        return self::SUCCESS;
    }
}
