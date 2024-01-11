<?php

namespace App\Jobs;

use App\Mail\UserRegistrationMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Mail;

class UserRegistration implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $email, $parsedSubject, $parsedBody, $parsedSignature, $actionText, $actionUrl;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($email, $parsedSubject, $parsedBody, $parsedSignature, $actionText, $actionUrl)
    {
        $this->email = $email;
        $this->actionText = $actionText;
        $this->actionUrl = $actionUrl;
        $this->parsedSubject = $parsedSubject;
        $this->parsedSignature = $parsedSignature;
        $this->parsedBody = $parsedBody;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::to($this->email)->send(new UserRegistrationMail($this->parsedSubject, $this->parsedBody, $this->parsedSignature, $this->actionText, $this->actionUrl));
    }
}
