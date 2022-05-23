<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

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

$factory->define(User::class, function (Faker $faker) {
    $firstName = $faker->firstName;
    $lastName = $faker->lastName;
    $name = $firstName . ' ' . $lastName;

    return [
        'name' => $name,
        'first_name' => $firstName,
        'last_name' => $lastName,
        'email' => $faker->unique()->safeEmail,
        'email_verified_at' => now(),
        'remember_token' => Str::random(10),
    ];
});
