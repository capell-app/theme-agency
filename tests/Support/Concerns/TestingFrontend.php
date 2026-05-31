<?php

declare(strict_types=1);

namespace Capell\Tests\Support\Concerns;

use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\Tests\AbstractTestCase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\View\Compilers\BladeCompiler;
use Livewire\Blaze\Blaze;
use Livewire\Blaze\BlazeServiceProvider;
use ReflectionProperty;

/**
 * @mixin AbstractTestCase
 */
trait TestingFrontend
{
    public function setUpTestingFrontend(): void
    {
        if (! App::environment('testing')) {
            return;
        }

        if (class_exists(Blaze::class) && class_exists(BlazeServiceProvider::class)) {
            if (! app()->bound('blaze')) {
                app()->register(BlazeServiceProvider::class);
            }

            Blaze::disable();
            Blaze::optimize()->clear();
        }

        $compiledViewPath = config('view.compiled');

        if (is_string($compiledViewPath) && $compiledViewPath !== '') {
            $compiledViewPath = $compiledViewPath . DIRECTORY_SEPARATOR . 'frontend-' . getmypid();

            config(['view.compiled' => $compiledViewPath]);

            if (app()->bound('blade.compiler')) {
                $bladeCompiler = app()->make('blade.compiler');

                if ($bladeCompiler instanceof BladeCompiler) {
                    $cachePath = new ReflectionProperty(BladeCompiler::class, 'cachePath');
                    $cachePath->setValue($bladeCompiler, $compiledViewPath);
                }
            }
        }

        if (is_string($compiledViewPath) && $compiledViewPath !== '' && File::isDirectory($compiledViewPath)) {
            File::cleanDirectory($compiledViewPath);
        }

        // Clear page cache storage if present
        /*try {
            $pageCache = resolve(PageCacheService::class);
            if ($pageCache->exists('')) {
                $pageCache->deleteDirectory('/');
            }
        } catch (Throwable) {
            // ignore
        }*/

        // Optionally could register routes if needed
        // \Capell\Frontend\Helpers\Routes::routes();

        $this->withoutVite();

        if (class_exists(ThemeRegistry::class) && app()->bound(ThemeRegistry::class)) {
            resolve(ThemeRegistry::class)->reset();
        }
    }
}
