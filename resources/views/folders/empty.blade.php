<div class="card">
    <div class="card-header">
        {{ __('Introducing Folders') }}
    </div>
    <div class="card-body">
        <h5 class="card-title">{{ __('Create Your First Folder') }}</h5>
        <p class="card-text">{{ __('You have not created any folders. Folders allow you to categorize your bookmarks and you need at least one folder to start adding bookmarks.') }}</p>

        <div class="d-flex bd-highlight mb-3">
            <a href="{{route('home')}}" class="btn btn-outline-secondary me-auto m-1"><i class="fa fa-chevron-left"></i> {{__('Back')}}</a>
            <a href="{{route('folders.create')}}" class="btn btn-outline-primary m-1">{{__('Create Folder')}}</a>
            <a href="#" class="btn btn-primary m-1">{{__('Give me the Recommended Folders')}}</a>
        </div>
    </div>
</div>
