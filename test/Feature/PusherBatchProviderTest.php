<?php

namespace Morloderex\PusherBatch\Tests\Feature;

use Mockery;
use Pusher\Pusher;
use Morloderex\PusherBatch\Broadcaster;
use Orchestra\Testbench\TestCase;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Broadcasting\BroadcastController;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PusherBatchProviderTest extends TestCase
{
    use RefreshDatabase;

    protected $broadcaster;

    public function setUp()
    {
        parent::setUp();

        $this->setUpBroadcaster();
        $this->listenOnBroadcastRoutes();
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['router']->post('/broadcasting/auth', BroadcastController::class . '@authenticate');
    }

    public function test_broadcast_driver()
    {
        $this->withoutExceptionHandling();


        $response = $this->actingAs(
            tap(new User, function ($user) {$user->id = 1;})
        )->call('POST', 'broadcasting/auth', [
            'socket_id' => 'testing',
            'channel_name' => [
                'private-post.1',
                'private-post.2',
                'presence-user.1',
                'presence-user.2',
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'private-post.1' => [
                'status' => 403,
            ],
            'private-post.2' => [
                'status' => 200,
                'data' => [
                    'auth' => 'testing',
                ]
            ],
            'presence-user.1' => [
                'status' => 200,
                'data' => [
                    'auth' => 'testing',
                    'channel_data'  => [
                        'user_id' => 1,
                        'user_info' => 1
                    ]
                ]
            ],
            'presence-user.2' => [
                'status' => 403,
            ]
        ]);
    }

    /**
     * Register my provider
     *
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [];
    }

    protected function setUpBroadcaster()
    {
        $pusher = Mockery::mock(Pusher::class);
        $pusher->shouldNotReceive('socket_auth')
            ->with('private-post.1', 'testing');

        $pusher->shouldReceive('presence_auth')
            ->once()
            ->with('presence-user.1', 'testing', 1, ['id' => 1])
            ->andReturn(json_encode(['auth' => 'testing', 'channel_data'  => ['user_id' => 1, 'user_info' => 1]]));

        $pusher->shouldNotReceive('presence_auth')
            ->with('presence-user.2', 'testing', 1, true);


        $pusher->shouldReceive('socket_auth')
            ->once()
            ->with('private-post.2', 'testing')
            ->andReturn(json_encode(['auth' => 'testing']));

        $broadcaster = new Broadcaster($pusher);

        Broadcast::swap($broadcaster);

        $this->broadcaster = $broadcaster;

        return $broadcaster;
    }

    protected function listenOnBroadcastRoutes()
    {
        $this->broadcaster->channel('user.{id}', function ($user, $id) {
            if ($id == 1) {
                return true;
            }

            return false;
        });

        $this->broadcaster->channel('post.{id}', function ($user, $id) {
            if ($id != 1) {
                return ['id', $user->id];
            }

            return false;
        });
    }
}