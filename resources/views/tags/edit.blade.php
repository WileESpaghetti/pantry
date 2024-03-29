@extends('layouts.app')

<!--
FIXME
merge with the create form

TODO
if this gets more complicated then it might be nice to add a reset button that sets everything back to the current
values
-->

@section('content')
    <form class="visually-hidden" id="delete-tag" method="POST" action="{{route('tags.destroy', $tag->id)}}">
        @csrf
        @method('DELETE')
    </form>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h1>{{__('Edit Tag: :name', ['name' => $tag->name])}}</h1>

                @error('error')
                <div class="alert alert-danger">
                    <ul class="list-unstyled mb-0">
                        <li>{{ $message }}</li>
                    </ul>
                </div>
                @enderror

                <form id="tag-edit" method="POST" action="{{ route('tags.update', $tag) }}">
                    @method('PUT')
                    @csrf

                    <div class="row mb-3">
                        <label for="name" class="form-label">{{__('Name')}}</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $tag->name) }}" required>
                        @error('name')
                            <p class="invalid-feedback">
                                {{$message}}
                            </p>
                        @enderror
                    </div>

                    <div class="row">
                        @if(!empty($tag))
                        <div class="col g-0">
                            <button type="submit" form="delete-tag"  class="btn btn-outline-danger me-auto m-1 ms-0">{{__('Delete Tag')}}</button>
                        </div>
                        @endif
                        <div class="col g-0 text-end">
                            <a href="{{url()->previous() != url()->current() ? url()->previous() : route('tags.index')}}" class="btn  btn-outline-secondary me-auto">{{__('Cancel')}}</a>
                            <button type="submit" class="btn btn-primary me-auto m-1">{{__('Save Tag')}}</button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
@endsection
