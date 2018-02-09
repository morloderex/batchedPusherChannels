<?php

namespace PusherBatch;

use Illuminate\Support\Str;
use Illuminate\Http\Response;
use Illuminate\Broadcasting\Broadcasters\PusherBroadcaster;

class Broadcaster extends PusherBroadcaster
{
    /**
     * Authenticate the incoming request for a given channel.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     */
    public function auth($request)
    {
        $channels = [];

        foreach($request->channel_name as $channel) {
            if (Str::startsWith($channel, ['private-', 'presence-']) && ! $request->user()) {
                $channels[$channel] = [
                    'status' => Response::HTTP_FORBIDDEN,
                ];
                continue;
            }

            $result = $this->verifyUserCanAccessChannel($request, $this->parseChannel($channel));

            if ($result) {
                $channels[$channel] = [
                    'status' => Response::HTTP_OK,
                    'data' => $this->validAuthenticationBatchedResponse($request, $channel, $result)
                ];
            } else {
                $channels[$channel] = [
                    'status' => Response::HTTP_FORBIDDEN
                ];
            }
        }

        return response()->json($channels);
    }

    /**
     * Authenticate the incoming request for a given channel.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $channel
     * @return mixed
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     */
    protected function verifyUserCanAccessChannel($request, $channel)
    {
        foreach ($this->channels as $pattern => $callback) {
            if (! Str::is(preg_replace('/\{(.*?)\}/', '*', $pattern), $channel)) {
                continue;
            }

            $parameters = $this->extractAuthParameters($pattern, $channel, $callback);

            if ($result = $callback($request->user(), ...$parameters)) {
                return $result;
            }
        }

        return false;
    }

    public function validAuthenticationBatchedResponse($request, $channel, $result)
    {
        if (Str::startsWith($channel, 'private')) {
            return $this->decodePusherResponse(
                $this->pusher->socket_auth($channel, $request->socket_id)
            );
        }

        return $this->decodePusherResponse(
            $this->pusher->presence_auth(
                $channel, $request->socket_id, $request->user()->getAuthIdentifier(), $result)
        );
    }

    /**
     * Return the valid authentication response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $result
     * @return mixed
     */
    public function validAuthenticationResponse($request, $result)
    {

    }

    /**
     * @param $channel
     * @return string
     */
    protected function parseChannel($channel)
    {
        return Str::startsWith($channel, 'private-')
            ? Str::replaceFirst('private-', '', $channel)
            : Str::replaceFirst('presence-', '', $channel);
    }
}