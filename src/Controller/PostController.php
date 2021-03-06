<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Core\Security;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

;

class PostController extends AbstractController
{
    private $security;
    private $translator;

    public function __construct(Security $security, TranslatorInterface $translator)
     {
         $this->security = $security;
         $this->translator = $translator;

     }

    public function index(Request $request,ManagerRegistry $doctrine,PaginatorInterface $paginator)
    {
        //search
        if($request->query->get('search')){
            $word = htmlspecialchars($request->query->get('search'));
            $posts = $doctrine->getRepository(Post::class)->createQueryBuilder('o')
                ->where('o.title LIKE :word')
                ->orWhere('o.descr LIKE :word')
                ->orderBY('o.created_at','desc')
                ->setParameter('word', '%'.$word.'%')
                ->getQuery()
                ->getResult();
        } else {
            $posts = $doctrine->getRepository(Post::class)->findAll();
        }

        return $this->render('posts/index.html.twig', [
            'pagination' => $paginator->paginate(
                $posts,$request->query->getInt('page', 1),4)
        ]);
    }


    public function create(Request $request, SluggerInterface $slugger, ManagerRegistry $doctrine)
    {
        $post = new Post();

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->saveToDB($post,$form, $doctrine, $slugger);
            return $this->redirectToRoute('post_index');
        }

        return $this->renderForm('posts/create.html.twig', [
            'form' => $form,
        ]);
    }

    public function show(int $id, ManagerRegistry $doctrine)
    {
        $post = $doctrine->getRepository(Post::class)->find($id);
        if(!$post){
            $this->addFlash('danger', $this->translator->trans('error.badPage'));
            return $this->redirectToRoute('post_index');
        }
        return $this->render('posts/show.html.twig',['post'=>$post]);
    }


    public function edit(int $id, Request $request, ManagerRegistry $doctrine,  SluggerInterface $slugger)
    {
        $post = $doctrine->getRepository(Post::class)->find($id);

        if($this->checkSecurity($post)){
            $form = $this->createForm(PostType::class, $post);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->saveToDB($post,$form, $doctrine, $slugger);
                return $this->redirectToRoute('post_index');
            }

            return $this->renderForm('posts/edit.html.twig', [
                'form' => $form,
            ]);
        }
        return $this->redirectToRoute('home_index');
    }


    public function saveToDB($post,$form, ManagerRegistry $doctrine, SluggerInterface $slugger)
    {

            $user = $this->getUser();
            $entityManager = $doctrine->getManager();
            $post->setTitle($form->get('title')->getData());
            $post->setShortTitle(mb_strlen($form->get('title')->getData()) > 30 ? substr($form->get('title')->getData(),0,30) . '...' :  $form->get('title')->getData());
            $post->setAuthorId($user->getId());
            $post->setDescr($form->get('descr')->getData());
            /** @var UploadedFile $img */
            $img = $form->get('img')->getData();

            $post->setCreatedAt(new \DateTime('@'.strtotime('now')));
            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($img) {
                $originalFilename = pathinfo($img->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$img->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $img->move(
                        $this->getParameter('post_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $post->setImg($newFilename);
            } else {
                //for update post, if I have image on BD and I don't have it in form. Save the same image
                if (!$post->getImg()){
                    $post->setImg('default.jpg');
                }
            }
            $entityManager->persist($post);
            $entityManager->flush();
            $id = $post->getId();

            if ($id){
                $this->addFlash('success',$this->translator->trans('error.saveSuccess'));
            } else {
                $this->addFlash('danger', $this->translator->trans('error.mistake'));
            }
            return true;
    }


    public function destroy(int $id,Request $request, ManagerRegistry $doctrine)
    {
        $submittedToken = $request->request->get('token');
        if ($this->isCsrfTokenValid('delete-item', $submittedToken)) {
            $post = $doctrine->getRepository(Post::class)->find($id);
            if($this->checkSecurity($post)){
                $imageName = $post->getImg();

                //remove file from catalogue
                if($imageName!='default.jpg'){
                    $fs = new Filesystem();
                    $fs->remove('uploads/posts/'.$imageName);
                }

                $this->addFlash('success', $this->translator->trans('error.deleteSuccess'));
                $entityManager = $doctrine->getManager();
                $entityManager->remove($post);
                $entityManager->flush();
            }
        } else {
            $this->addFlash('danger', $this->translator->trans('error.invalidToken'));
        }

        return $this->redirectToRoute('post_index');

    }

    //check if I have permission and post is exist.
    public function checkSecurity($post){

        if(!$post){
            $this->addFlash('danger', $this->translator->trans('error.badPage'));
            return false;
        }
        if(!$this->security->isGranted('ROLE_USER')){
            $this->addFlash('danger', $this->translator->trans('error.permission'));
            return false;
        }
        return true;
    }
}
