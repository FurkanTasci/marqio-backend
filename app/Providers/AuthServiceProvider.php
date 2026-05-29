<?php 

namespace App\Providers;

use App\Models\Bookmark;
use App\Policies\BookmarkPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Die Policy-Model-Mapping für die Anwendung.
     *
     * @var array
     */
    protected $policies = [
        Bookmark::class => BookmarkPolicy::class,
    ];

    /**
     * Registiere alle Policies für die Anwendung.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // Hier kannst du weitere Gates definieren, falls nötig
        Gate::define('delete', [BookmarkPolicy::class, 'delete']);
    }
}
