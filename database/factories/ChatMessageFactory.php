<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\User;
use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(ChatMessage::class, function (Faker $faker) {
    return [
        'chat_id' => Chat::first()->id,
        'sender_user_id' => User::all()->random()->id,
        'message' => $faker->text(20),
    ];
});
