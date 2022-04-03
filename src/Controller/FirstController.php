<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FirstController extends AbstractController
{
   // #[Route('/first', name: 'app_first')]
    public function index(): Response
    {
        $number = random_int(0, 100);

        return new Response(
            '<html><head></head><body>Lucky number: '.$number.'</body></html>'
        );
    }
}
