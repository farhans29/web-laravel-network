<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Router;

class ContentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Share the CRM_ISS data globally
        $this->CRM_ISS = DB::table('m_company')->select('nilai')->where('kunci', 'Nama OS')->first();

        view()->composer('*', function ($view) {
            // Check if the user is authenticated before accessing Auth::user()
            if (auth()->check()) {
                $userId = auth()->user()->id;
                $groupId = auth()->user()->idusergrouping;
                
                // Retrieve routers for the authenticated user's group
                $dataRouters = Router::where('idusergrouping', $groupId)->get();
                
                // dd($dataRouters);
            } else {
                $userId = null;
                $groupId = null;
                $dataRouters = collect(); // Return an empty collection
            }

            // Pass data to all views
            $view->with([
                'CRM_ISS' => $this->CRM_ISS,
                'userId'      => $userId,
                'groupId'     => $groupId,
                'dataRouters' => $dataRouters
            ]);
        });
    }

}
