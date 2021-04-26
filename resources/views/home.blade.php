@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">

            @foreach ($notifications as $notification)
                <div class="alert alert-info text-info">
                    <h4 class="alert-heading">{{ __($notification->type)}}</h4>
                    <p><strong>{{$notification->data['count']}}</strong> {{ __('bookmarks imported')  }}</p>
                </div>
            @endforeach

            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">

                    {{ __('You are logged in!') }}

                </div>
            </div>

            @include('dashboard.file-upload')

        </div>
    </div>
</div>
@endsection
