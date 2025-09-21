<?php

namespace Ersalak\Sms\Facade;

use Illuminate\Support\Facades\Facade;

class ErsalakSmsFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Ersalak\Sms\SmsClient::class;
    }
}
