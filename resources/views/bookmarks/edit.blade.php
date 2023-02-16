@extends('layouts.app')

<!--
FIXME
merge with the create form

TODO
if this gets more complicated then it might be nice to add a reset button that sets everything back to the current
values

TODO
add folder select

TODO
add tags
-->
@section('content')
    <form class="visually-hidden" id="delete-bookmark" method="POST" action="{{route('bookmarks.destroy', $bookmark->id)}}">
        @csrf
        @method('DELETE')
    </form>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h1>{{__('Edit Bookmark')}}</h1>

                @error('error')
                    <div class="alert alert-danger">
                        <ul class="list-unstyled mb-0">
                            <li>{{ $message }}</li>
                        </ul>
                    </div>
                @enderror

                <form id="bookmark-edit" method="POST" action="{{ route('bookmarks.update', $bookmark) }}">
                    @method('PUT')
                    @csrf

                    <div class="row mb-3">
                        <label for="url" class="form-label">{{__('URL')}}</label>
                        <input type="text" class="form-control @error('url') is-invalid @enderror" id="url" name="url" value="{{ old('url', $bookmark->url) }}" required>
                        @error('url')
                        <p class="invalid-feedback">
                            {{$message}}
                        </p>
                        @enderror
                    </div>

                    <div class="row mb-3">
                        <label for="name" class="form-label">{{__('Title')}}</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $bookmark->name) }}" >
                        @error('name')
                        <p class="invalid-feedback">
                            {{$message}}
                        </p>
                        @enderror
                    </div>

                    <div class="row mb-3">
                        <label for="description" class="form-label">{{__('Description')}}</label>
                        <input type="text" class="form-control @error('description') is-invalid @enderror" id="description" name="description" value="{{ old('description', $bookmark->description) }}" >
                        @error('description')
                        <p class="invalid-feedback">
                            {{$message}}
                        </p>
                        @enderror
                    </div>

                    <div class="row mb-3">
                        <label for="description" class="form-label">{{__('Description')}}</label>
                        <input type="text" class="form-control" id="tags" name="tags" placeholder="Choose tags" value="{{$bookmark->tags->implode('name', ', ')}}">
{{--                        <input type="text" class="form-control @error('description') is-invalid @enderror" id="description" name="description" value="{{ old('description', $bookmark->description) }}" >--}}
                        @error('tags')
                        <p class="invalid-feedback">
                            {{$message}}
                        </p>
                        @enderror
                    </div>

                    <div class="row">
                        @if(!empty($bookmark))
                            <div class="col g-0">
                                <button type="submit" form="delete-bookmark"  class="btn btn-outline-danger me-auto m-1 ms-0">{{__('Delete Bookmark')}}</button>
                            </div>
                        @endif
                        <div class="col g-0 text-end">
                            <a href="{{url()->previous() != url()->current() ? url()->previous() : route('bookmarks.index')}}" class="btn  btn-outline-secondary me-auto">{{__('Cancel')}}</a>
                            <button type="submit" class="btn btn-primary me-auto m-1">{{__('Save Bookmark')}}</button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
@endsection
