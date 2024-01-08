<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendForgetPasswordEmailJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $otp;

    /**
     * Create a new job instance.
     *
     * @param  \App\User  $user
     * @param  int  $otp
     * @return void
     */
    public function __construct($user, $otp)
    {
        $this->user = $user;
        $this->otp = $otp;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
         Log::info('Start handling SendForgetPasswordEmailJob.');
    
    // Your existing code here

  
       $data = ['otp' => $this->otp];
        Mail::send('mail.forget_password', $data, function ($message) {
    $message->to($this->user->email, $this->user->name)->subject('Forget Password');
    $message->from(env('FROM_EMAIL'), env('APP_NAME'));
});

          Log::info('End handling SendForgetPasswordEmailJob.');
    }
}
