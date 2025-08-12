<?php

namespace App\Console\Commands;

use App\Models\Organization;
use App\Services\OrganizationService;
use Illuminate\Console\Command;

class CreateStripeCustomers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'organization:stripeCustomers {organization_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create stripe customer for each organization that doesn\'t have one.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $updated = 0;
        $organizations = [];

        if ($organizationId = $this->argument('organization_id')) {
            $organizations = [Organization::findOrFail($organizationId)];
        } else {
            $organizations = Organization::all();
        }

        foreach ($organizations as $organization) {
            if ($organization->stripe_customer_id) {
                $this->info("Skipping organization #{$organization->id} - already has a stripe_customer_id");

                continue;
            }

            $this->info("Updating #{$organization->id}");
            OrganizationService::createStripeCustomer($organization);
            $updated++;
        }

        $this->info("Updated {$updated} organizations");

        return Command::SUCCESS;
    }
}
