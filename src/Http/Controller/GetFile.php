<?php

namespace Kdubuc\Storage\Http\Controller;

use Assert\Assert;
use GuzzleHttp\Psr7\Response;
use League\Flysystem\Filesystem;
use Psr\Http\Message\ResponseInterface;
use function GuzzleHttp\Psr7\stream_for;
use Psr\Http\Message\ServerRequestInterface;

final class GetFile
{
    /**
     * Initialize HTTP controller.
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * Récupération du contenu d'un fichier.
     */
    public function __invoke(ServerRequestInterface $request, array $args = []) : ResponseInterface
    {
        // Récupération du nom de fichier à récupérer
        $filename = $args['filename'];
        Assert::that($filename)->notEmpty()->string();

        // Contrôle de l'existence du fichier
        if(!$this->filesystem->has($filename)) {
            return (new Response())->withStatus(404);
        }

        return (new Response())
            ->withBody(stream_for($this->filesystem->read($filename)))
            ->withHeader('Content-Type', $this->filesystem->getMimetype($filename));
    }
}
