<?php
// app/Providers/AuthServiceProvider.php
namespace App\Providers;

use App\Models\User;
use App\Models\Account;
use App\Models\Asset;
use App\Policies\UserPolicy;
use App\Policies\AccountPolicy;
use App\Policies\AssetPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Account::class => AccountPolicy::class,
        Asset::class => AssetPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        //
    }
}