<div class="card">
    <div class="card-header">
        {{ __('Introducing Tags') }}
    </div>
    <div class="card-body">
        <h5 class="card-title">{{ __('Create Your First Tag') }}</h5>
        <p class="card-text">{{ __('You have not created any tags. Tags allow you to categorize your bookmarks.') }}</p>

        <div class="d-flex bd-highlight mb-3">
            <a href="{{route('home')}}" class="btn btn-outline-secondary me-auto m-1"><i class="fa fa-chevron-left"></i> {{__('Back')}}</a>
            <a href="{{route('tags.create')}}" class="btn btn-outline-primary m-1">{{__('Create Tag')}}</a>
            <a href="#" class="btn btn-primary m-1">{{__('Give me the Recommended Tags')}}</a>
        </div>
    </div>
</div>
