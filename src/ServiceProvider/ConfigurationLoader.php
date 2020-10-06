<?php

namespace Kdubuc\Storage\ServiceProvider;

use UMA\DIC\Container;
use UMA\DIC\ServiceProvider;

final class ConfigurationLoader implements ServiceProvider
{
    /**
     * Registers services on the given app.
     */
    public function provide(Container $container) : void
    {
        $container->set('config', [
            'debug'              => (bool) getenv('API_ENV'),
            'url'                => getenv('API_URL'),
            'max_file_size'      => $this->returnBytes(ini_get('upload_max_filesize')),
            'filesystem.options' => [
                'path' => '/usr/data/',
            ],
        ]);
    }

    /**
     * Registers services on the given app.
     * https://www.php.net/manual/en/function.ini-get.php#96996.
     */
    private function returnBytes(string $size_str) : int
    {
        switch (mb_substr($size_str, -1)) {
            case 'M': case 'm': return (int) $size_str * 1048576;
            case 'K': case 'k': return (int) $size_str * 1024;
            case 'G': case 'g': return (int) $size_str * 1073741824;
            default: return $size_str;
        }
    }
}
