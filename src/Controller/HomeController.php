<?php

namespace App\Controller;

use App\Entity\Contacts;
use App\Form\ContactType;
use App\Form\PostType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home_index")
     */
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

    public function contact(Request $request, ManagerRegistry $doctrine) : Response
    {

        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $id = $this->sendMessage($form, $request, $doctrine);
            if ($id){
                return $this->redirectToRoute('mail_sendMail', ['id' => $id]);
            } else {
                $this->addFlash('danger', 'error.mistake');
                return $this->redirectToRoute('home_index', ['max' => 10]);
            }
        }


        return $this->renderForm('contact.html.twig', [
            'form' => $form,
        ]);
    }

    public function sendMessage($form, $request, $doctrine): bool|int
    {
            $entityManager = $doctrine->getManager();
            $content = new Contacts();
            $content->setAuthorIp($request->request->get('ip'));
            $content->setName($form->get('name')->getData());
            $content->setEmail($form->get('email')->getData());
            if (!empty($form->get('phone')->getData())) {
                $content->setPhone($form->get('phone')->getData());
            }
            $content->setText($form->get('message')->getData());
            $content->setTimestamps(new \DateTime("now"));
            $entityManager->persist($content);
            $entityManager->flush();
            $id = $content->getId();
            if ($id){
                return $id;
            }
            return false;
    }
}
