<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    return ['id' => $user->id, 'name' => $user->name, 'chat' => $chatId];
});

Broadcast::channel('room.{roomId}', function ($user, $roomId) {
    return ['id' => $user->id, 'name' => $user->name, 'room' => $roomId];
});

Broadcast::channel('webinar.{webinarId}', function ($user, $webinarId) {
    return ['id' => $user->id, 'name' => $user->name, 'webinar' => $webinarId];
});
