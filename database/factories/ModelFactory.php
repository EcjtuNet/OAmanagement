<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define(App\User::class, function (Faker\Generator $faker) {
    return [
        'name'           => 'admin',
        'phone'          => '10000000000',
        'student_id'     => null,
        'password'       => bcrypt('iampassword'),
        'confirmed'      => true,
        'is_admin'       => true,
        'remember_token' => str_random(10),
        'created_at'     => \Carbon\Carbon::now(),
        'updated_at'     => \Carbon\Carbon::now(),
    ];
});

$factory->define(App\OrganizationTag::class, function (Faker\Generator $faker) {
    return [
        'name'       => str_random(5),
        'created_at' => \Carbon\Carbon::now(),
        'updated_at' => \Carbon\Carbon::now(),
    ];
});
