<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{
    public function index(Request $request)
    {
        if($request->search){
            $posts =  Post::join('users','author_id', '=', 'users.id')
                ->where('title','like','%'.$request->search.'%')
                ->orWhere('descr','like','%'.$request->search.'%')
                ->orderBY('posts.created_at','desc')
                ->get();
            return view('posts.index', compact('posts'));
        }

        $posts = Post::join('users','author_id', '=', 'users.id')
            ->orderBY('posts.created_at','desc')
            ->paginate(4);

        return view('posts.index', compact('posts'));
    }


    public function create()
    {
        return view('posts.create');
    }


    //for check field we need create request php artisan make:request PostRequest
    public function store(PostRequest $request)
    {
        $post = new Post();
        $post->title = $request->title;
        $post->short_title = \Str::length($request->title)>30 ? \Str::substr($request->title,0,30). '...' : $request->title;
        $post->author_id =\Auth::user()->id;
        $post->descr = $request->descr;
        if($request->file('img')){
            $path = \Storage::putFile('public', $request->file('img'));
            $url = \Storage::url($path);
            $post->img = $url;
        }
        $post->save();

        return redirect()->route('post.index')->with('success','Post zapisano z succesem!');
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
