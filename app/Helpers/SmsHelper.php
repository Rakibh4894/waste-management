<?php

namespace App\Helpers;

use App\Jobs\SendMessageJob;

class SmsHelper
{
    /**
     * Send SMS via queued job
     *
     * @param string $to
     * @param string $message
     */
    public static function send($to, $message)
    {
        dispatch(new SendMessageJob('sms', $to, $message));
    }
}
