<?php

namespace Kdubuc\Storage\ServiceProvider;

use League\Flysystem;
use UMA\DIC\Container;
use UMA\DIC\ServiceProvider;

final class FileSystem implements ServiceProvider
{
    /**
     * Registers services on the given app.
     */
    public function provide(Container $container) : void
    {
        // Initialize filesystem abstraction interface
        $container->set('filesystem', function (Container $container) : Flysystem\Filesystem {
            $adapter    = new Flysystem\Adapter\Local($container->get('config')['filesystem.options']['path']);
            $filesystem = new Flysystem\Filesystem($adapter);

            return $filesystem;
        });
    }
}
