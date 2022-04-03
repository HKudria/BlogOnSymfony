<?php

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MailController extends AbstractController
{
    public function sendMail(Request $request) {
        $contact = Contact::findOrFail($request->id);
        Mail::to(env('MAIL_WHO_GET_MAIL'))->send(new SendMessage($contact));
        return redirect()->route('home')->with('success','Widomość wyslana pomyslnie. Wkrótce skontaktuję się z tobą');
    }
}
