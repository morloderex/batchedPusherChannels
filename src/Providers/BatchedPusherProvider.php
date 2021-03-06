<?php

namespace Morloderex\PusherBatch\Providers;

use Pusher\Pusher;
use Morloderex\PusherBatch\Broadcaster;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Broadcast;

class BatchedPusherProvider extends ServiceProvider
{
    public function boot()
    {
        Broadcast::extend('BatchedPusher', function ($app, $config) {
            return new Broadcaster(
                new Pusher($config['key'], $config['secret'],
                    $config['app_id'], $config['options'] ?? [])
            );
        });
    }
}
