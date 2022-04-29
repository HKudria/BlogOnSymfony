@extends('laravel.resources.views.layouts.layout', ['title' => $post->title])

@section('content')

    <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header"><h2>{{$post->title}}</h2></div>
                    <div class="card-body">
                        <div class="card-img card-img-max" style="background-image: url({{$post->img ?? asset('/img/default.jpg')}})"></div>
                        <div class="card-author">Author: {{$post->name}}</div>
                        <div class="card-author">Post created at: {{$post->created_at->diffForHumans()}}</div>
                        <p class="card-text c">{{$post->descr}}</p>
                        <a href="{{route('post.index')}}" class="btn btn-outline-primary">Main page</a>
                        @auth()
                            @if(Auth::user()->id == $post->author_id || \Auth::user()->role == 'admin')
                                <a href="{{route('post.edit',['id'=> $post->post_id])}}" class="btn btn-outline-success">Edit post</a>
                                <form action="{{route('post.destroy',['id'=> $post->post_id])}}" method="post" class="form-delete" onsubmit="if (confirm('Do you realy want to delete this post?')) {return true} else {return false}">
                                    @csrf
                                    @method('DELETE')
                                    <input type="submit" class="btn btn-outline-danger" value="Delete">
                                </form>
                            @endif
                        @endauth
                    </div>


                </div>
            </div>
    </div>

@endsection
