<?php


use App\Models\Banner;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\User;
use App\Models\Webinar;
use Illuminate\Database\Seeder;

/**
 * Class BouncerSeeder
 */
class BouncerSeeder extends Seeder
{
    public function run()
    {
        /**
         * Superadmin
         */
        Bouncer::allow('superadmin')->everything();

        /**
         * Owner
         */
        Bouncer::allow('owner')->toOwnEverything();
        Bouncer::allow('owner')->toManage(Banner::class);
        Bouncer::allow('owner')->to('view-employee', User::class);
        Bouncer::allow('owner')->to('create-employee', User::class);
        Bouncer::allow('owner')->to('update-employee', User::class);
        Bouncer::allow('owner')->to('delete-employee', User::class);
        Bouncer::allow('owner')->to('view-webinars', Webinar::class);
        Bouncer::allow('owner')->to('view-webinar', Webinar::class);
        Bouncer::allow('owner')->to('block-chat', Chat::class);
        Bouncer::allow('owner')->to('unblock-chat', Chat::class);
        Bouncer::allow('owner')->to('post-message', Webinar::class);
        Bouncer::allow('owner')->to('delete-message', ChatMessage::class);
        Bouncer::allow('owner')->to('ban-user', Webinar::class);

        /**
         * Moderator
         */
        Bouncer::allow('moderator')->to('view-webinar', Webinar::class);
        Bouncer::allow('moderator')->to('block-chat', Chat::class);
        Bouncer::allow('moderator')->to('unblock-chat', Chat::class);
        Bouncer::allow('moderator')->to('post-message', Webinar::class);
        Bouncer::allow('moderator')->to('delete-message', ChatMessage::class);
        Bouncer::allow('moderator')->to('ban-user', Webinar::class);

        /**
         * Visitor
         */
        Bouncer::allow('visitor')->to('view-webinar', Webinar::class);
        Bouncer::allow('visitor')->to('post-message', Webinar::class);
    }
}