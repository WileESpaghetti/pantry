<!--
FIXME
filter bar should work

FIXME
Headers should be sortable?
-->
@if (count($tags) < 1)
    <div class="container mt-5">
        @include('tags.empty')
    </div>
@else
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
                    <a href="{{route('tags.create')}}" class="btn btn-primary">
                        <i class="fa fa-plus"></i>
                        New
                    </a>
                    {{--            <button type="button" class="btn btn-secondary">2</button>--}}
                    {{--            <button type="button" class="btn btn-secondary">3</button>--}}
                    {{--            <button type="button" class="btn btn-secondary">4</button>--}}
                </div>
            </div>

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
                    <th scope="row"><i class="fa fa-fw fa-circle" style="color: {{ $data->color }}"></i> {{ $data->id }}</th>
                    <td>{{-- link to the show() route --}}{{ $data->name }}</td>
                    <td>
                        <a href="{{ route('tags.edit', $data->id) }}"><i class="fa fa-edit"></i></a>

                        <!-- TODO replace with button and confirmation dialog -->
                        <a href="{{ route('tags.destroy', $data->id) }}" class="ms-2"><i class="fa fa-trash"></i></a>
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
            {!! $tags->links() !!}
        </div>
    </div>
@endif
