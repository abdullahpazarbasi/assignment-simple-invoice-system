<?php

namespace App\Contracts;

use Illuminate\Contracts\Redis\Factory;

interface MessagePublisher extends Factory
{
    /**
     * @param string $channel
     * @param string $message
     * @return \Redis|int|false
     */
    public function publish(string $channel, string $message);
}
