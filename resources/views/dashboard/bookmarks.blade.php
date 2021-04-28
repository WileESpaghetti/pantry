<div class="container mt-5">
    {{-- Pagination --}}
    <div class="d-flex justify-content-center">
        {!! $bookmarks->links() !!}
    </div>

    <table class="table table-bordered">
        <thead class="thead-light">
        <tr>
            <th scope="col">#</th>
            <th scope="col">Title</th>
            <th scope="col">URI</th>
            <th scope="col">Description</th>
        </tr>
        </thead>
        <tbody>
        @foreach($bookmarks as $data)
            <tr>
                <th scope="row">{{ $data->id }}</th>
                <td>{{ $data->title }}</td>
                <td>{{ $data->uri }}</td>
                <td>{{ $data->note }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{-- Pagination --}}
    <div class="d-flex justify-content-center">
        {!! $bookmarks->links() !!}
    </div>
</div>
