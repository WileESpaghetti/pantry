<!--
FIXME
filter bar should work

FIXME
Headers should be sortable?
-->
<div class="container mt-5">
    @if (count($folders) < 1)
        @include('folders.empty')
    @else
{{-- Pagination --}}
<div class="d-flex flex-row justify-content-between">
    <form class="form-inline my-2 my-lg-0">
        <div class="input-group mb-3">
            <div class="input-group-prepend">
                <span class="input-group-text" id="basic-addon1">Filter</span>
            </div>
            <input class="form-control mr-sm-2" type="search" placeholder="folder name contains" aria-label="filter by folder name">
        </div>
    </form>
    <div class="btn-toolbar mb-3" role="toolbar" aria-label="Toolbar with button groups">
        <div class="btn-group" role="group" aria-label="First group">
            <a href="{{route('folders.create')}}" class="btn btn-primary">
                <i class="fa fa-plus"></i>
                New
            </a>
{{--            <button type="button" class="btn btn-secondary">2</button>--}}
{{--            <button type="button" class="btn btn-secondary">3</button>--}}
{{--            <button type="button" class="btn btn-secondary">4</button>--}}
        </div>
    </div>

    {!! $folders->links() !!}
</div>


<table class="table table-bordered">
    <thead class="thead-light">
    <tr>
        <th scope="col">#</th>
        <th scope="col">Name</th>
        <th scope="col">Links</th>
        <th scope="col">Actions</th>
    </tr>
    </thead>
    <tbody>
    @foreach($folders as $data)
        <tr>
            <th scope="row"><i class="fa fa-fw fa-circle" style="color: {{ "#" . $data->color }}"></i> {{ $data->id }}</th>
            <td>{{-- link to the show() route --}}{{ $data->name }}</td>
            <td>{{ $data->links }}</td>
            <td>
                <a href="{{ route('folders.edit', $data->id) }}"><i class="fa fa-edit"></i></a>

                <!-- TODO replace with button and confirmation dialog -->
                <a href="{{ route('folders.destroy', $data->id) }}" class="ms-2"><i class="fa fa-trash"></i></a>
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
    {!! $folders->links() !!}
</div>
</div>
@endif
