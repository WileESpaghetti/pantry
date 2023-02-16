<!--
FIXME
filter bar should work

FIXME
Headers should be sortable?
-->
@if(session('errors'))
    <div class="alert alert-danger">
        <ul class="list-unstyled mb-0">
            @foreach (session('errors') as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    <!-- TODO show delete success message -->
@endif
@if (empty($isContainer) && count($bookmarks) < 1)
    <div class="container mt-5">
        @include('bookmarks.empty')
    </div>
@else
<form class="visually-hidden" id="delete-bookmark" method="POST">
    @csrf
    @method('DELETE')
</form>
<div class="container mt-5">
    {{-- Pagination --}}
    <div class="d-flex flex-row justify-content-between">
        <form class="form-inline my-2 my-lg-0">
            <div class="input-group mb-3">
                <div class="input-group-prepend">
                    <span class="input-group-text" id="basic-addon1">Filter</span>
                </div>
                <input class="form-control mr-sm-2" type="search" placeholder="bookmark title or URL contains" aria-label="filter by title or description">
            </div>
        </form>
        <div class="btn-toolbar mb-3" role="toolbar" aria-label="Toolbar with button groups">
            <div class="btn-group" role="group" aria-label="First group">
                <a href="{{route('bookmarks.create')}}" class="btn btn-primary">
                    <i class="fa fa-plus"></i>
                    New
                </a>
            </div>
        </div>

        {!! $bookmarks->links() !!}
    </div>


    <table class="table table-bordered">
        <thead class="thead-light">
        <tr>
            <th scope="col">#</th>
            <th scope="col">Title</th>
            <th scope="col">URL</th>
            <th scope="col">Description</th>
            <th scope="col">Tags</th>
            <th scope="col">Actions</th>
        </tr>
        </thead>
        <tbody>
        @foreach($bookmarks as $data)
            <tr>
                <th scope="row">{{ $data->id }}</th>
                <td>{{ $data->name }}</td>
                <td>{{ $data->url }}</td>
                <td>{{ $data->description }}</td>
                <td>
                    @foreach($data->tags as $tag)
                        <a href="{{route('tags.show', ['tag' => $tag->id])}}">{{ $tag->name }}</a>
                    @endforeach
                </td>
                <td>
                    <a href="{{ route('bookmarks.edit', $data->id) }}" class="btn btn-link p-0"><i class="fa fa-edit"></i></a>

                    <!-- TODO add confirmation dialog -->
                    <button type="submit" class="btn btn-link p-0 ms-2" form="delete-bookmark" formaction="{{route('bookmarks.destroy', $data->id)}}" ><i class="fa fa-trash"></i></button>
                    {{-- view on larder --}}
                    {{-- import from larder --}}
                    {{-- delete --}}
                    {{-- empty --}}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{-- Pagination --}}
    <div class="d-flex justify-content-center">
        {!! $bookmarks->links() !!}
    </div>
</div>
@endif
