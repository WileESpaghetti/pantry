@extends('layouts.app')

<!--
FIXME
merge with the create form

TODO
if this gets more complicated then it might be nice to add a reset button that sets everything back to the current
values

FIXME
not entirely happy with error handling
-->

@section('content')
    <form class="visually-hidden" id="delete-folder" method="POST" action="{{route('folders.destroy', $folder->id)}}">
        @csrf
        @method('DELETE')
    </form>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h1>{{__('Edit Folder: :name', ['name' => $folder->name])}}</h1>

                @error('error')
                <div class="alert alert-danger">
                    <ul class="list-unstyled mb-0">
                        <li>{{ $message }}</li>
                    </ul>
                </div>
                @enderror

                <form id="folder-edit" method="POST" action="{{ route('folders.update', $folder) }}">
                    @method('PUT')
                    @csrf

                    <div class="row mb-3">
                        <label for="name" class="form-label">{{__('Name')}}</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $folder->name) }}" required>
                        @error('name')
                            <p class="invalid-feedback">
                                {{$message}}
                            </p>
                        @enderror
                    </div>

                    <div class="row">
                        @if(!empty($folder))
                            <div class="col g-0">
                                <button type="submit" form="delete-folder"  class="btn btn-outline-danger me-auto m-1 ms-0">{{__('Delete folder')}}</button>
                            </div>
                        @endif
                        <div class="col g-0 text-end">
                            <a href="{{url()->previous() != url()->current() ? url()->previous() : route('folders.index')}}" class="btn  btn-outline-secondary me-auto">{{__('Cancel')}}</a>
                            <button type="submit" class="btn btn-primary me-auto m-1">{{__('Save Folder')}}</button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
@endsection
