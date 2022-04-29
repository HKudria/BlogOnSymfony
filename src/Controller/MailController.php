<?php

namespace App\Controller;


use App\Entity\Contacts;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class MailController extends AbstractController
{
    public function sendMail(int $id, ManagerRegistry $doctrine, MailerInterface $mailer) {

        $contact = $doctrine->getRepository(Contacts::class)->find($id);

        if (!$contact) {
            $this->addFlash('danger', 'Wystąpil bląd. Prosze sprobować póżniej');
            return $this->redirectToRoute('home_index');
        } else {
            $name = $contact->getName();
            $mail = $contact->getEmail();
            $phone = $contact->getPhone();
            $text = $contact->getText();
            $email = (new Email())
                ->from('nadia-masage@com.pl')
                ->to('h.kudrya@hotmail.com')
                ->replyTo($contact->getEmail())
                ->subject('Kontact w sprawie masażu')
                ->html(<<<MAIL
                            Nowa wiadomość <br> 
                            Imię: $name <br>       
                            Mail: $mail <br>
                            tel.: $phone <br>
                            <p> $text </p> 
                        MAIL);

            $mailer->send($email);
            $this->addFlash('success','Widomość wyslana pomyslnie. Wkrótce skontaktuję się z tobą');
            return $this->redirectToRoute('home_index');
        }

    }
}
