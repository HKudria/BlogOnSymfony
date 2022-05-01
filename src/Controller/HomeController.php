<?php

namespace App\Controller;

use App\Entity\Contacts;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    public function index() : Response
    {
        return $this->render('home.html.twig');
    }

    public function price() : Response
    {
        return $this->render('price.html.twig');
    }

    public function about() : Response
    {
        return $this->render('about.html.twig');
    }

    public function contact() : Response
    {
        return $this->render('contact.html.twig');
    }

    public function sendMessage(Request $request, ManagerRegistry $doctrine)
    {
        $submittedToken = $request->request->get('token');
        if ($this->isCsrfTokenValid('send-mail', $submittedToken)) {

            $entityManager = $doctrine->getManager();
            $content = new Contacts();
            $content->setAuthorIp($request->request->get('ip'));
            $content->setName($request->request->get('name'));
            $content->setEmail($request->request->get('email'));
            if (!empty($request->request->get('phone'))) {
                $content->setPhone($request->request->get('phone'));
            }
            $content->setText($request->request->get('text'));
            $content->setTimestamps(new \DateTime("now"));
            $entityManager->persist($content);
            $entityManager->flush();
            $id = $content->getId();
            if ($id){
                return $this->redirectToRoute('mail_sendMail', ['id' => $id]);
            } else {
                $this->addFlash('danger', 'Wystąpil bląd. Prosze sprobować póżniej');
                return $this->redirectToRoute('home_index', ['max' => 10]);
            }
        }
    }
}