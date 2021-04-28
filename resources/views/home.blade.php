@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">

            @foreach ($notifications as $notification)
                <div class="alert alert-info text-info">
                    <h4 class="alert-heading">{{ __($notification->type)}}</h4>
                    <p><strong>{{$notification->data['counts']['total']}}</strong> {{ __('bookmarks processed')  }}</p>
                    <p><strong>{{$notification->data['counts']['inserted']}}</strong> {{ __('new')  }}</p>
                    <p><strong>{{$notification->data['counts']['updated']}}</strong> {{ __('updated')  }}</p>
                    <p><strong>{{$notification->data['counts']['skipped']}}</strong> {{ __('skipped')  }}</p>
                    <p><strong>{{$notification->data['warnings']}}</strong></p>
                </div>
            @endforeach

            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">

                    {{ __('You are logged in!') }}

                </div>
            </div>

            @include('dashboard.file-upload')

            @include('dashboard.bookmarks')

        </div>
    </div>
</div>
@endsection
