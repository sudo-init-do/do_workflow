<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Seed the demo workflow
        $this->call(DemoWorkflowSeeder::class);

        // Seed a demo HTTP action (Slack / API call example)
        $this->call(DemoHttpActionSeeder::class);

        $this->call(DemoChainedActionsSeeder::class);
    }
}
