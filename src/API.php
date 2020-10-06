<?php

namespace Kdubuc\Storage;

use UMA\DIC\Container;
use League\Route\Router;
use Middlewares as VendorMiddleware;
use Http\Factory\Guzzle\StreamFactory;
use Psr\Http\Message\ResponseInterface;
use Http\Factory\Guzzle\ResponseFactory;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class API extends Container implements RequestHandlerInterface
{
    /**
     * Initialisation du pipeline HTTP.
     */
    public function __construct()
    {
        // Services injection.
        $this->register(new ServiceProvider\ConfigurationLoader());
        $this->register(new ServiceProvider\FileSystem());
    }

    /**
     * La requête passe dans un pipeline HTTP afin de produire une réponse.
     */
    public function handle(ServerRequestInterface $server_request) : ResponseInterface
    {
        // Initialisation du pipeline HTTP via la création d'un router
        // Middleware is code that exists between the request and response,
        // and which can take the incoming request, perform actions based on it,
        // and either complete the response or pass delegation on to the next middleware in the queue.
        // Pipeline is FIFO (First middleware IN, First middleware executed)
        $router = new Router();

        // Le pipeline retourne les exceptions uniquement sous forme HTTP 5xx / 4xx
        $router->middleware(new VendorMiddleware\ErrorHandler([
            new VendorMiddleware\ErrorFormatter\JsonFormatter(new ResponseFactory(), new StreamFactory()),
        ]));

        // Gzip / Deflate Encoders
        $router->middlewares([
            new VendorMiddleware\GzipEncoder(new StreamFactory()),
            new VendorMiddleware\DeflateEncoder(new StreamFactory()),
        ]);

        // Healthcheck route
        $router->head('/_healthcheck', new Http\Controller\HealthCheck($this->get('filesystem')));

        // API routes
        $router->post('/files', new Http\Controller\UploadFile($this->get('filesystem'), $this->get('config')['max_file_size'], $this->get('config')['url']));
        $router->get('/files/{filename}', new Http\Controller\GetFile($this->get('filesystem')));

        // Handle the request ! o/
        return $router->dispatch($server_request);
    }
}
