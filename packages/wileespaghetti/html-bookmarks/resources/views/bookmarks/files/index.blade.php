@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">

                <h1>{{__('Manage Bookmark File Uploads')}}</h1>

                @if(session()->has('success'))
                    <p class="alert alert-success">
                        {{ session()->get('success') }}
                    </p>
                @endif

                @include('htmlbookmarks::bookmarks.files.empty')

            </div>
        </div>
    </div>
@endsection
