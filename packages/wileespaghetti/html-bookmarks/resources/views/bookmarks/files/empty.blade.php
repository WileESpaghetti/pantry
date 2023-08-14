<div class="card">
    <div class="card-header">
        {{ __('Introducing Bookmark File Imports') }}
    </div>
    <div class="card-body">
        <h5 class="card-title">{{ __('Upload Your First Bookmark File') }}</h5>
        <p class="card-text">{{ __('You have not uploaded any bookmark files.') }}</p>

        <div class="d-flex bd-highlight mb-3">
            <a href="{{route('home')}}" class="btn btn-outline-secondary me-auto m-1"><i class="fa fa-chevron-left"></i> {{__('Back')}}</a>
            <!-- FIXME show the import form here -->
        </div>
    </div>
</div>
