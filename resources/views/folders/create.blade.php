@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h1>{{__('Create Folder')}}</h1>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="list-unstyled mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('folders.store') }}">
                    @csrf

                    <div class="row mb-3">
                        <label for="name" class="form-label">{{__('Name')}}</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="@if (old('name')) {{old('name')}} @endif" required>
                        @error('name')
                            <p class="invalid-feedback">
                                {{$message}}
                            </p>
                        @enderror
                    </div>

                    <div class="row">
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
