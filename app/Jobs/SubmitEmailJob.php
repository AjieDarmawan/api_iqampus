<?php

namespace App\Jobs;

use App\Mail\FormEmail;
use Illuminate\Support\Facades\Mail;

class SubmitEmailJob extends Job
{
    protected $to;
    protected $content;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($to,$content)
    {
        $this->to = $to;
        $this->content = $content;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::to($this->to)->send(new FormEmail($this->content));
    }
}
