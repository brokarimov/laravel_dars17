<?php

namespace App\Jobs;

use Http;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendMessegePhone implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public $token;
    public $data1;
    public function __construct($token, $data1)
    {
        $this->token = $token;
        $this->data1 = $data1;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $response = Http::withToken($this->token)->post('notify.eskiz.uz/api/message/sms/send', $this->data1);
    }
}
