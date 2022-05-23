<?php

use App\Models\ChatMessage;
use Illuminate\Database\Seeder;

/**
 * Class ChatMessageSeeder
 */
class ChatMessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(ChatMessage::class, 10000)->create();
    }
}
