<?php
namespace Smartel1\ArrayFixer;

use Illuminate\Support\ServiceProvider;

class ArrayFixerServiceProvider extends ServiceProvider
{
    public function boot()
    {

    }

    public function register()
    {
        $this->app->bind('Smartel1\ArrayFixer\ArrayFixer', function ($app) {

            return new \Smartel1\ArrayFixer\ArrayFixer( new \Smartel1\ArrayFixer\ArrayFixerRules);

        });

    }


}
