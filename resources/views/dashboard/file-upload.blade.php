@php use function Pantry\Services\humanBytes; @endphp

{{--
TODO
have an option for `Create a new folder called 'Imported'` menu option

TODO
show max file upload limit

TODO
frontend validation for filesize
--}}
<div class="card mt-3">
    <div class="card-header">{{ __('Upload Bookmarks File') }}</div>

    <div class="card-body">
        @error('upload')
            <div class="alert alert-danger" role="alert">{{ $message }}</div>
        @enderror
        @if(session()->has('success'))
            <div class="alert alert-success">
                {{ __(session()->get('success')) }}
            </div>
        @endif

        <form method="POST" action="{{ route('bookmarks.import') }}" enctype="multipart/form-data">
            <label for="bookmark" class="form-label">{{ __('Select file to upload') }} ({{ humanBytes($uploadLimit) }}
                )</label>
            <div class="input-group">
                <input type="file" class="form-control" name="bookmark" id="bookmark" accept="text/html">
                <button type="submit" class="btn btn-primary">Upload</button>
            </div>

            @csrf
        </form>
    </div>
</div>
