<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class RateLimittingTest extends TestCase
{
    public function testRateLimitting()
    {
        $success = RateLimiter::attempt("send-message-1",5,function(){
            echo "Send message 1";
        });
        self::assertTrue($success);
    }
}
