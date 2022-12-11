@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h1>{{__('Create Bookmark')}}</h1>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="list-unstyled mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('bookmarks.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label">{{__('URL')}}</label>
                        <input type="text" class="form-control @error('url') is-invalid @enderror" id="url" name="url" value="@if (old('url')) {{old('url')}} @endif" required>
                        @error('url')
                        <p class="invalid-feedback">
                            {{$message}}
                        </p>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="title" class="form-label">{{__('Title')}}</label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="@if (old('title')) {{old('title')}} @endif" >
                        @error('title')
                        <p class="invalid-feedback">
                            {{$message}}
                        </p>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">{{__('Description')}}</label>
                        <input type="text" class="form-control @error('description') is-invalid @enderror" id="description" name="description" value="@if (old('description')) {{old('description')}} @endif" >
                        @error('description')
                        <p class="invalid-feedback">
                            {{$message}}
                        </p>
                        @enderror
                    </div>

                    <a href="{{url()->previous() != url()->current() ? url()->previous() : route('bookmarks.index')}}" class="btn  btn-outline-secondary me-auto">{{__('Cancel')}}</a>
                    <button type="submit" class="btn btn-primary me-auto m-1">{{__('Save Bookmark')}}</button>
                </form>

            </div>
        </div>
    </div>
@endsection