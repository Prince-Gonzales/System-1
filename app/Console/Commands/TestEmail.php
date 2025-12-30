<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationCodeMail;

class TestEmail extends Command
{
    protected $signature = 'test:email {email}';
    protected $description = 'Test email sending functionality';

    public function handle()
    {
        $email = $this->argument('email');
        $testCode = '123456';

        try {
            $this->info('Sending test email to: ' . $email);
            Mail::to($email)->send(new VerificationCodeMail($testCode, 'Test Email - Password Reset'));
            $this->info('✓ Email sent successfully!');
            $this->info('Check your inbox (and spam folder) for the test email.');
            return 0;
        } catch (\Exception $e) {
            $this->error('✗ Failed to send email: ' . $e->getMessage());
            $this->error('Please check your .env MAIL configuration.');
            return 1;
        }
    }
}

