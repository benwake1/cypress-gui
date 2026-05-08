<?php

namespace App\Console\Commands;

use App\Mail\CancellationMail;
use App\Mail\ResizeCompleteMail;
use App\Mail\WelcomeMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendTransactionalEmail extends Command
{
    protected $signature = 'send:transactional-email
                            {--type= : welcome|resize-complete|cancellation}
                            {--email= : Recipient email address}
                            {--password= : Admin password (welcome only)}
                            {--url= : Dashboard URL}
                            {--from-plan= : Old plan name (resize-complete only)}
                            {--to-plan= : New plan name (resize-complete only)}';

    protected $description = 'Send a transactional email to a customer';

    public function handle(): int
    {
        $type  = $this->option('type');
        $email = $this->option('email');

        if (empty($type) || empty($email)) {
            $this->error('--type and --email are required.');
            return 1;
        }

        match ($type) {
            'welcome'         => $this->sendWelcome($email),
            'resize-complete' => $this->sendResizeComplete($email),
            'cancellation'    => $this->sendCancellation($email),
            default           => $this->bail("Unknown type: {$type}"),
        };

        $this->info("Email sent: type={$type} to={$email}");
        return 0;
    }

    private function sendWelcome(string $email): void
    {
        $url      = $this->option('url') ?? config('app.url');
        $password = $this->option('password') ?? '';

        Mail::to($email)->send(new WelcomeMail($email, $url, $password));
    }

    private function sendResizeComplete(string $email): void
    {
        $fromPlan = $this->option('from-plan') ?? 'unknown';
        $toPlan   = $this->option('to-plan')   ?? 'unknown';
        $url      = $this->option('url') ?? config('app.url');

        Mail::to($email)->send(new ResizeCompleteMail($email, $fromPlan, $toPlan, $url));
    }

    private function sendCancellation(string $email): void
    {
        Mail::to($email)->send(new CancellationMail($email));
    }

    private function bail(string $message): never
    {
        $this->error($message);
        exit(1);
    }
}
