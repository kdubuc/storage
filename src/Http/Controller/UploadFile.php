<?php

namespace Kdubuc\Storage\Http\Controller;

use Assert\Assert;
use GuzzleHttp\Psr7\Response;
use Mimey\MimeTypes as Mimey;
use League\Flysystem\Filesystem;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class UploadFile
{
    /**
     * Initialize HTTP controller.
     */
    public function __construct(Filesystem $filesystem, int $max_file_size, string $location_url)
    {
        $this->filesystem    = $filesystem;
        $this->max_file_size = $max_file_size;
        $this->location_url  = $location_url;
    }

    /**
     * Upload d'un fichier.
     */
    public function __invoke(ServerRequestInterface $request) : ResponseInterface
    {
        // Récupération du body de la requête
        $request_body = $request->getParsedBody();
        
        // Controle de la taille du fichier uploadé
        Assert::that($request->getBody()->getSize())->lessThan($this->max_file_size);

        // Contrôle de la présence du header Content-Type
        Assert::that($request->hasHeader('Content-Type'))->true();

        // Get the extension that matches the given MIME type
        $extension = (new Mimey())->getExtension($request->getHeaderLine('Content-Type'));

        // If extension cannot be found, we abort the upload
        Assert::that($extension)->notNull();

        // Récupération du contenu du fichier
        $contents = $request->getBody()->__toString();
        
        // On s'assure que le contenu n'est pas vide
        Assert::that($contents, "Contents is empty")->notEmpty()->string();

        // Contruction du nom de fichier (basé sur le hash du fichier afin de garantir
        // l'unicité du contenu et de ne pas poluer le volume avec des doublons).
        $filename = sprintf('%s.%s', hash('md5', $contents), $extension);

        // Si le fichier existe déjà, on retourne sa location sans créer quoique ce soit
        if ($this->filesystem->has($filename)) {
            return (new Response())->withStatus(200)->withHeader('Location', $this->location_url."/$filename");
        }

        // Le fichier n'existe pas, on le crée donc dans le filesystem
        $this->filesystem->write($filename, $contents);

        // On retourne la réponse
        return (new Response())->withStatus(201)->withHeader('Location', $this->location_url."/$filename");
    }
}
