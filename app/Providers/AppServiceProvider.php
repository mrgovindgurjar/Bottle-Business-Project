<?php

namespace App\Providers;

use App\Models\Customer;
use App\Policies\CustomerPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Models\RecurringPlan;
use App\Models\DailyOrder;
use App\Policies\RecurringPlanPolicy;
use App\Policies\DailyOrderPolicy;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Customer::class, CustomerPolicy::class);
        Gate::policy(RecurringPlan::class, RecurringPlanPolicy::class);
        Gate::policy(DailyOrder::class, DailyOrderPolicy::class);
    }
}
