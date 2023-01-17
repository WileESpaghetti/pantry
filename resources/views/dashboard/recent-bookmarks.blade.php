<div class="card mt-3">
    <div class="card-header">
        {{ __('Recent Bookmarks') }}
    </div>

    <table class="table mb-0">
        <tbody>
        @foreach($bookmarks as $bookmark)
            <tr>
                <td>@if($bookmark->name) {{$bookmark->name}} @else {{$bookmark->url}}@endif</td>
                <!-- TODO tags or folder? -->
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="card-footer text-end border-top-0">
        <a href="{{route('bookmarks.index')}}">{{ __('View all') }}</a>
    </div>
</div>
