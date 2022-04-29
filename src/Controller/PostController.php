<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class PostController extends AbstractController
{
    public function index(Request $request)
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
        return $this->render('posts/index.html.twig');
    }


    public function create(Request $request, SluggerInterface $slugger, ManagerRegistry $doctrine)
    {
        $post = new Post();

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
//        $submittedToken = $request->request->get('create_form');

        if ($form->isSubmitted() && $form->isValid()) {
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
            }
            $entityManager->persist($post);
            $entityManager->flush();
            $id = $post->getId();

            if ($id){
                $this->addFlash('success','Post zapisano z succesem!');
            } else {
                $this->addFlash('danger', 'Wystąpil bląd. Prosze sprobować póżniej');
            }
            return $this->redirectToRoute('post_index');
        }
        return $this->renderForm('posts/create.html.twig', [
            'form' => $form,
        ]);
//        return $this->render('');
    }

    public function show($id)
    {
        $post = Post::join('users','author_id', '=', 'users.id')
            ->find($id);
        if(!$post){
            return redirect()->route('post.index')->withErrors('Nie poprawna strona!');
        }
        return view('posts.show',compact('post'));
    }


    public function edit($id)
    {
        $post = Post::find($id);
        if(!$post){
            return redirect()->route('post.index')->withErrors('Nie poprawna strona!');
        }
        if($post->author_id != \Auth::user()->id  && \Auth::user()->role != 'admin'){
            return redirect()->route('post.index')->withErrors('You don\'t have permission for it!');
        }
        return view('posts.edit',compact('post'));
    }


    public function update(Request $request, $id)
    {
        $post = Post::find($id);
        if(!$post){
            return redirect()->route('post.index')->withErrors('Nie poprawna strona!');
        }
        if($post->author_id != \Auth::user()->id && \Auth::user()->role != 'admin'){
            return redirect()->route('post.index')->withErrors('You don\'t have permission for it!');
        }

        $post->title = $request->title;
        $post->short_title = \Str::length($request->title)>30 ? \Str::substr($request->title,0,30). '...' : $request->title;
        $post->descr = $request->descr;


        if($request->file('img')){
            $path = \Storage::putFile('public', $request->file('img'));
            $url = \Storage::url($path);
            $post->img = $url;
        }
        $post->update();
        $id = $post->post_id;
        return redirect()->route('post.show', compact('id'))->with('success','Post was edited successful!');

    }


    public function destroy($id)
    {
        $post = Post::find($id);
        if(!$post){
            return redirect()->route('post.index')->withErrors('This page isn\'t correct');
        }
        if($post->author_id != \Auth::user()->id  && \Auth::user()->role != 'admin'){
            return redirect()->route('post.index')->withErrors('You don\'t have permission for it!');
        }
        $post->delete();

        return redirect()->route('post.index')->with('success','Post was deleted successful!');

    }
}
