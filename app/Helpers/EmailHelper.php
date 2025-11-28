<?php

namespace App\Helpers;

use App\Jobs\SendMessageJob;

class EmailHelper
{
    /**
     * Send email via queued job
     *
     * @param string $to
     * @param string $subject
     * @param string $body
     */
    public static function send($to, $subject, $body)
    {
        dispatch(new SendMessageJob('email', $to, $body, $subject));
    }
}
