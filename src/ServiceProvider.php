<?php

namespace Msleonar\Gitexport;

use Illuminate\Support\Facades\Blade;
use Msleonar\Gitexport\Console\ExportGitVersion;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     */
    public function boot()
    {
        // Publish the git config to show
        $this->publishes([
            __DIR__.'/../config/git.php' => config_path('git.php'),
        ]);

        Blade::directive('githash', function ($length) {
            if (!is_int($length)) {
                $length = config('git.hash_length');
            }

            return "<?php echo substr(config('git.hash'), 0, $length); ?>";
        });
        Blade::directive('gitdate', function ($format) {
            if (!is_string($format) || empty(trim($format))) {
                $format = config('git.commit_date_format');
            }

            return "<?php echo date($format, config('git.date')); ?>";
        });

        $this->commands([
            ExportGitVersion::class,
        ]);
    }

    /**
     * Register bindings in the container.
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/git.php', 'git'
        );
    }
}
