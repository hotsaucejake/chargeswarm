<?php

$factory->define(\Rennokki\Chargeswarm\Test\Models\User::class, function () {
    return [
        'name' => 'Name'.str_random(5),
        'email' => str_random(5).'@gmail.com',
        'password' => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // secret
        'remember_token' => str_random(10),
    ];
});
