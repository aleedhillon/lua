<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Carbon\CarbonPeriod;

use App\Enums\User\Role;

use App\Models\Plan;
use App\Models\User;
use App\Models\Link;
use App\Models\LinkStat;
use App\Models\Workspace;

class LocalDevelopmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::factory([
            'name' => 'Admin',
            'email' => 'admin@lua.sh'
        ])
            ->hasAttached(
                Workspace::factory([
                    'plan_id' => Plan::where('internal_id', 'free')->first()->id
                ]),
                ['role' => Role::ROLE_ADMIN]
            )
            ->create();

        $workspace = Workspace::first();

        // set current workspace
        $user->current_workspace_id = $workspace->id;
        $user->save();

        // create some links
        $links = Link::factory()
            ->count(10)
            ->create([
                'workspace_id' => $workspace->id
            ]);

        echo "Creating LinkStat records for " . $links->count() . " links...\n";

        // Create a reasonable amount of stats for testing (10-50 per link)
        foreach ($links as $index => $link) {
            $statsCount = rand(10, 50);

            // Create stats for this link using the factory
            LinkStat::factory()
                ->count($statsCount)
                ->create([
                    'link_id' => $link->id,
                    'workspace_id' => $workspace->id,
                    'created_at' => now()->subDays(rand(1, 30))
                ]);

            // Update the link click count
            $link->update(['clicks' => $statsCount]);

            echo "Created $statsCount stats for link " . ($index + 1) . "/" . $links->count() . "\n";
        }

        echo "Local development seeding completed successfully!\n";
    }
}
