<?php

namespace Database\Seeders;

use App\Models\PostReply;
use Illuminate\Database\Seeder;

class PostReplySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PostReply::factory(5)->create();
        PostReply::factory(5)->create();
        PostReply::factory(5)->create();
        PostReply::factory(5)->create();
    }
}
