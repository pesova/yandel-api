<?php

namespace App\Providers\Custom;

use Gate;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class CustomProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        /**
         // TODO: Consider auto binding all service files in services folder
         * with all interface files in contracts folder.
         * Also helps to force developers to follow the
         * coding-to-interface standard.
         */
        
        $this->app->bind('App\Contracts\AuthServiceInterface', 'App\Services\AuthService');
        
        $this->app->bind('App\Contracts\TokenServiceInterface', 'App\Services\TokenService');

        $this->app->bind('App\Contracts\UserServiceInterface', 'App\Services\UserService');

        $this->app->bind('App\Contracts\NotificationServiceInterface', 'App\Services\NotificationService');

    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //custom search macro
        Builder::macro('whereLike', function ($attributes, string $searchTerm) {
            $this->where(function (Builder $query) use ($attributes, $searchTerm) {
                foreach (Arr::wrap($attributes) as $attribute) {
                    $query->orWhere($attribute, 'LIKE', "%{$searchTerm}%");
                }
            });

            return $this;
        });

        //custom filter macro
        Builder::macro('whereFilter', function (array $filters) {
            $this->where(function (Builder $query) use ($filters) {
                foreach ($filters as $identifier=>$value) {
                    // handle case where value is a json object (array) having 'range' property
                    // meaning a whereBetween clause should be applied to its identifier
                    if( is_array($value) && $value['range'] ) {
                        $query->whereBetween($identifier,[\Carbon\Carbon::parse($value['range'][0])
                            ->startOfDay(),\Carbon\Carbon::parse($value['range'][1])->endOfDay()]);
                    }
                    // handle general cases where value is an array, by just using a whereIn clause
                    elseif (is_array($value) ) $query->whereIn($identifier, $value);
                    // any other case outside the above should just use a where clause
                    else $query->whereIn( $identifier, explode(',',  $value) );
                }
            });

            return $this;
        });

        /**
         * Extending eloquent to allow searching by multiple columns
         */
        Builder::macro('whereColumns', function (array $columns, $value) {
            $this->where(function (Builder $query) use ($columns, $value) {
                foreach ($columns as $column) {
                    $query->orWhere($column, $value);
                }
            });

            return $this;
        });


        /**
         *  Log sql queries for performance debugging on local environment
         */
        if(app()->environment('local')){
            \DB::listen(function($query) {
                \Log::channel('db')->info('Query log',
                    [
                        'performance'=>$query->time,
                        'query'=>$query->sql,
                        'bindings'=>$query->bindings
                    ]
                );
            });
        }
    }
}
