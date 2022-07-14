{{--
TODO
have an option for `Create a new folder called 'Imported'` menu option

TODO
show max file upload limit
--}}
<div class="card mt-3">
    <div class="card-header">{{ __('Upload Bookmarks File') }}</div>

    <div class="card-body">
        @if (session('errors'))
            <div class="alert alert-danger" role="alert">
                {{ session('errors') }}
            </div>
        @endif
        @if(session()->has('success'))
            <div class="alert alert-success">
                {{ __(session()->get('success')) }}
            </div>
        @endif

        <form method="POST" action="bookmarks/import" enctype="multipart/form-data">
            <label for="bookmark" class="form-label">{{ __('Select file to upload') }}</label>
            <div class="input-group">
                <input type="file" class="form-control" name="bookmark" id="bookmark" accept="text/html">
                <button type="submit" class="btn btn-primary">Upload</button>
            </div>

            @csrf
        </form>
    </div>
</div>
