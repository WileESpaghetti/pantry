<!--
FIXME
use tag name as header

FIXME
filter bar should work

FIXME
Headers should be sortable?

FIXME
error does not display when a validation error occurs
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

@if (count($tags) < 1)
    <div class="container mt-5">
        @include('tags.empty')
    </div>
@else
    <form class="visually-hidden" id="delete-tag" method="POST">
        <!-- FIXME see if we can utilize the tags.deleteMany route instead -->
        @csrf
        @method('DELETE')
    </form>
    <form name="delete-many" id="delete-many" action="{{route('tags.destroyMany')}}" method="POST">
        <!-- FIXME display confirmation -->
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
                    <input class="form-control mr-sm-2" type="search" placeholder="tag name contains" aria-label="filter by tag name">
                </div>
            </form>

            <div class="btn-toolbar mb-3" role="toolbar" aria-label="Toolbar with button groups">
                <div class="btn-group" role="group" aria-label="First group">
                    <button class="btn btn-danger text-light" type="submit" form="delete-many">
                        <i class="fa fa-trash"></i>
                        Delete selected tags
                    </button>
                    <a href="{{route('tags.create')}}" class="btn btn-primary">
                        <i class="fa fa-plus"></i>
                        New
                    </a>
                    {{--            <button type="button" class="btn btn-secondary">2</button>--}}
                    {{--            <button type="button" class="btn btn-secondary">3</button>--}}
                    {{--            <button type="button" class="btn btn-secondary">4</button>--}}
                </div>
            </div>

        </div>

        <div class="d-flex flex-row justify-content-around">
            {!! $tags->links() !!}
        </div>



        <table class="table table-bordered">
            <thead class="thead-light">
            <tr>
                <th scope="col">#</th>
                <th scope="col">Name</th>
                <th scope="col">Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach($tags as $data)
                <tr>
                    <th scope="row">
                        <input type="checkbox" name="tags[]" value="{{$data->id}}" form="delete-many">
                        <i class="fa fa-fw fa-circle" style="color: {{ $data->color }}"></i>
                        {{ $data->id }}
                    </th>
                    <td><a href="{{route('tags.show', $data->id)}}">{{ $data->name }}</a></td>
                    <td>
                        <a href="{{ route('tags.edit', $data->id) }}" class="btn btn-link p-0"><i class="fa fa-edit"></i></a>

                        <!-- TODO add confirmation dialog -->
                        <button type="submit" class="btn btn-link p-0 ms-2" form="delete-tag" formaction="{{route('tags.destroy', $data->id)}}" ><i class="fa fa-trash"></i></button>
                        {{-- view on larder --}}
                        {{-- import from larder --}}
                        {{-- empty --}}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        {{-- Pagination --}}
        <div class="d-flex justify-content-center">
            {!! $tags->links() !!}
        </div>
    </div>
@endif
