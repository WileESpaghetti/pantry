<div class="card mt-3">
    <div class="card-header">{{ __('Upload Bookmarks File') }}</div>

    <div class="card-body">
        @if (session('error'))
            <div class="alert alert-danger" role="alert">
                {{ session('error') }}
            </div>
        @endif
        @if(session()->has('success'))
            <div class="alert alert-success">
                {{ session()->get('success') }}
            </div>
        @endif
        <form method="POST" action="bookmarks/import" enctype="multipart/form-data">
            <div class="form-group">
                <label for="bookmarks">{{ __('Select file to upload') }}</label>
                <input type="file" class="form-control-file" name="bookmark" id="bookmark" accept="text/html">
            </div>

            <button type="submit" class="btn btn-primary">Upload</button>

            @csrf
        </form>
    </div>
</div>
