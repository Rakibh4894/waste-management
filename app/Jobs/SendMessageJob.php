<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use App\Models\MessageLog;

class SendMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $type; // email or sms
    public $to;
    public $message;
    public $subject;

    public function __construct($type, $to, $message, $subject = null)
    {
        $this->type = $type;
        $this->to = $to;
        $this->message = $message;
        $this->subject = $subject;
    }

    public function handle()
    {
        try {
            if ($this->type === 'email') {
                Mail::raw($this->message, function ($mail) {
                    $mail->to($this->to)->subject($this->subject ?? 'Notification');
                });
            } elseif ($this->type === 'sms') {
                Http::get("https://api.greenweb.com.bd/api.php", [
                    'token' => env('SMS_API_KEY'),
                    'to' => $this->to,
                    'message' => $this->message,
                ]);
            }

            MessageLog::create([
                'type' => $this->type,
                'to' => $this->to,
                'message' => $this->message,
                'subject' => $this->subject,
                'status' => 1
            ]);
        } catch (\Exception $e) {
            MessageLog::create([
                'type' => $this->type,
                'to' => $this->to,
                'message' => $this->message,
                'subject' => $this->subject,
                'status' => 0,
                'error' => $e->getMessage()
            ]);
        }
    }
}
