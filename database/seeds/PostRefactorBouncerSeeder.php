<?php

use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Class PostRefactorBouncerSeeder
 */
class PostRefactorBouncerSeeder extends Seeder
{

    public function run()
    {
        /**
         * Owner
         */
        Bouncer::allow('owner')->to(User::PERMISSION_ABILITY_VIEW_ROOMS, Room::class);
        Bouncer::allow('owner')->to(User::PERMISSION_ABILITY_VIEW_ROOM, Room::class);
    }
}
