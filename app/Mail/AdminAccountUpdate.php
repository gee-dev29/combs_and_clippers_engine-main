<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Admin;

class AdminAccountUpdate extends Mailable
{
    use Queueable, SerializesModels;

    public $admin;
    public $changes;
    public $updateType;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Admin $admin, $changes, $updateType)
    {
        $this->admin = $admin;
        $this->changes = $changes;
        $this->updateType = $updateType;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = 'Your Admin Account has been Updated';

        if ($this->updateType === 'role_update') {
            $subject = 'Your Admin Account Permissions Updated';
        } elseif ($this->updateType === 'password_change') {
            $subject = 'Your Admin Account Password Changed';
        }

        return $this->markdown('emails.admin-account-update')
            ->subject($subject)
            ->with([
                'admin' => $this->admin,
                'changes' => $this->changes,
                'updateType' => $this->updateType,
                'loginUrl' => route('login')
            ]);
    }
}