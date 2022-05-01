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

class PostController extends AbstractController
{
    private $security;

    public function __construct(Security $security)
     {
         $this->security = $security;
     }

    public function index(ManagerRegistry $doctrine)
    {
//        if($request->search){
//            $posts =  Post::join('users','author_id', '=', 'users.id')
//                ->where('title','like','%'.$request->search.'%')
//                ->orWhere('descr','like','%'.$request->search.'%')
//                ->orderBY('posts.created_at','desc')
//                ->get();
//            return view('posts.index', compact('posts'));
//        }
//
//        $posts = Post::join('users','author_id', '=', 'users.id')
//            ->orderBY('posts.created_at','desc')
//            ->paginate(4);
//

        return $this->render('posts/index.html.twig', ['posts' => $doctrine->getRepository(Post::class)->findAll()]);
    }


    public function create(Request $request, SluggerInterface $slugger, ManagerRegistry $doctrine)
    {
        $post = new Post();

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->saveToDB($post,$form, $request, $doctrine, $slugger);
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
            $this->addFlash('danger', 'Nie poprawna strona!');
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
                $this->saveToDB($post,$form, $request, $doctrine, $slugger);
                return $this->redirectToRoute('post_index');
            }

            return $this->renderForm('posts/edit.html.twig', [
                'form' => $form,
            ]);
        }
        return $this->redirectToRoute('home_index');
    }


    public function saveToDB($post,$form,Request $request, ManagerRegistry $doctrine, SluggerInterface $slugger)
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
                $post->setImg('default.jpg');
            }
            $entityManager->persist($post);
            $entityManager->flush();
            $id = $post->getId();

            if ($id){
                $this->addFlash('success','Post zapisano z succesem!');
            } else {
                $this->addFlash('danger', 'Wystąpil bląd. Prosze sprobować póżniej');
            }
            return true;
    }


    public function destroy($id, Request $request, ManagerRegistry $doctrine)
    {

        $post = $doctrine->getRepository(Post::class)->find($id);
        if($this->checkSecurity($post)){
            $imageName = $post->getImg();

            //remove file from catalogue
            if($imageName!='default.jpg'){
                $fs = new Filesystem();
                $fs->remove('uploads/posts/'.$imageName);
            }

            $this->addFlash('success', 'Post był wycofany pomyslnie');
            $entityManager = $doctrine->getManager();
            $entityManager->remove($post);
            $entityManager->flush();
        }

        return $this->redirectToRoute('post_index');

    }

    public function checkSecurity($post){

        if(!$post){
            $this->addFlash('danger', 'Nie poprawna strona!');
            return false;
        }
        if(!$this->security->isGranted('ROLE_USER')){
            $this->addFlash('danger', 'You don\'t have permission for it!');
            return false;
        }

        return true;
    }
}
