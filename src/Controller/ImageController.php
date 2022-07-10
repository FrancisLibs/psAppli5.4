<?php

namespace App\Controller;

use League\Glide\ServerFactory;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use League\Glide\Responses\SymfonyResponseFactory;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ImageController extends AbstractController
{
    #[Route('/images/{path}', name: 'show')]
    public function show(Filesystem $filesystem, Request $request, string $path): StreamedResponse
    {
        dd($path);
        $server=ServerFactory::create([
            'response'  =>  new SymfonyResponseFactory($request),
            'source'    =>  $filesystem,
            'cache'     =>  $filesystem,
            'cache_path_prefix' => '.cache',
            'base_url'  =>  'images',
        ]);

        return $server->getImageResponse($path, $request->request->all());
    }
}
