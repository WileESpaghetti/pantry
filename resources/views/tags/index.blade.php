@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">

                <h1>Tags</h1>

                @if(session()->has('success'))
                    <p class="alert alert-success">
                        {{ session()->get('success') }}
                    </p>
                @endif

                {{--
                 TODO
                 import from larder

                 TODO
                 manage button  - screen is the edit screen from larder

                 TODO
                 filter list
                --}}


                @include('tags.list')

            </div>
        </div>
    </div>
@endsection
