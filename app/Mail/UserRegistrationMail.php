<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserRegistrationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $template_subject;
    public $template_body;
    public $template_signature;
    public $actionText;
    public $actionUrl;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($template_subject,$template_body,$template_signature,$actionText,$actionUrl)
    {
        $this->template_subject   = $template_subject;
        $this->template_body      = $template_body;
        $this->template_signature = $template_signature;
        $this->actionText         = $actionText;
        $this->actionUrl          = $actionUrl;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->template_subject)->markdown('emails.emialTemplate',['template_body'=>$this->template_body,'template_signature'=>$this->template_signature,'actionText'=>$this->actionText,'actionUrl'=>$this->actionUrl]);
    }
}
