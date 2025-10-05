<?php

namespace App\Supports\Traits;

use App\Repositories\Sms;

trait SmsTrait
{
    protected function sendSMS($to, $msg)
    {
        $sms_provider = env("SMS_PROVIDER");
        if ($sms_provider == "termii") {
            return Sms::sendWithTermii($to, $msg);
        } elseif ($sms_provider == "sleengshort") {
            return Sms::sendWithSleengShort($to, $msg);
        } elseif ($sms_provider == "nuobjects") {
            return Sms::sendWithNuobject($to, $msg);
        } else {
            return Sms::sendWithTermii($to, $msg);
        }
    }
}
