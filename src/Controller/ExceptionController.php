<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\ErrorHandler\Exception\FlattenException;

class ExceptionController extends AbstractController
{
    public function showException(FlattenException $exception): Response
    {
        $statusCode = $exception->getStatusCode();

        switch ($statusCode) {
            case 403:
                return $this->render('bundles\TwigBundle\Exception\error403.html.twig', [
                    'status_code' => $statusCode,
                    'status_text' => Response::$statusTexts[$statusCode] ?? 'Erreur',
                ]);
            case 404:
                return $this->render('bundles\TwigBundle\Exception\error404.html.twig', [
                    'status_code' => $statusCode,
                    'status_text' => Response::$statusTexts[$statusCode] ?? 'Erreur',
                ]);
            case 500:
                return $this->render('bundles\TwigBundle\Exception\error500.html.twig', [
                    'status_code' => $statusCode,
                    'status_text' => Response::$statusTexts[$statusCode] ?? 'Erreur',
                ]);
            default:
                return $this->render('bundles\TwigBundle\Exception\error.html.php', [
                    'status_code' => $statusCode,
                    'status_text' => Response::$statusTexts[$statusCode] ?? 'Erreur',
                ]);
        }
    }
}
