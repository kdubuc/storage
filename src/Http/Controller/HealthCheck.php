<?php

namespace Kdubuc\Storage\Http\Controller;

use GuzzleHttp\Psr7\Response;
use League\Flysystem\Filesystem;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class HealthCheck
{
    /**
     * Initialize HTTP controller.
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * HealthCheck endpoint.
     */
    public function __invoke(ServerRequestInterface $request, array $args = []) : ResponseInterface
    {
        return (new Response())->withStatus(200);
    }
}
