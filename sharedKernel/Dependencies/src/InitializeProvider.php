<?php

declare(strict_types=1);

namespace SharedKernel\Dependencies;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\App;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class InitializeProvider extends ServiceProvider
{
    public function boot(): void
    {
        $initializers = $this->getInitializers();
        foreach ($initializers as $interface => $class) {
            App::bind($interface, $class);
        }
    }

    private function getInitializers(): array
    {
        $initializers = $this->initializeFeatures([]);

        return $this->initializeSharedKernel($initializers);
    }

    public function initializeFeatures($initializers)
    {
        $found = glob(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'features' . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Dependencies' . DIRECTORY_SEPARATOR . 'initialize.php');

        foreach ($found as $path) {
            $initializer = require($path);
            $initializers = array_merge($initializers, $initializer);
        }

        return $initializers;
    }

    public function initializeSharedKernel($initializers)
    {
        $path = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
        $directory = new RecursiveDirectoryIterator($path);
        $iterator = new RecursiveIteratorIterator($directory);

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }
            if (!preg_match('/[src\/Dependencies\/initialize\.php]$/u', $file->getFilename())) {
                continue;
            }
            $initializer = require_once($file->getRealPath());
            if (!is_array($initializer)) {
                continue;
            }
            $initializers = array_merge($initializers, $initializer);
        }

        return $initializers;
    }
}
