<!--
FIXME
use name as header

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

@if (count($folders) < 1)
    <div class="container mt-5">
        @include('folders.empty')
    </div>
@else
<form class="visually-hidden" id="delete-folder" method="POST">
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
                <a href="{{ route('folders.edit', $data->id) }}" class="btn btn-link p-0"><i class="fa fa-edit"></i></a>

                <!-- TODO add confirmation dialog -->
                <button type="submit" class="btn btn-link p-0 ms-2" form="delete-folder" formaction="{{route('folders.destroy', $data->id)}}" ><i class="fa fa-trash"></i></button>
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


        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal" data-bs-whatever="@mdo">Open modal for @mdo</button>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal" data-bs-whatever="@fat">Open modal for @fat</button>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal" data-bs-whatever="@getbootstrap">Open modal for @getbootstrap</button>

        <div class="modal " id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">New message</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form>
                            <div class="mb-3">
                                <label for="recipient-name" class="col-form-label">Recipient:</label>
                                <input type="text" class="form-control" id="recipient-name">
                            </div>
                            <div class="mb-3">
                                <label for="message-text" class="col-form-label">Message:</label>
                                <textarea class="form-control" id="message-text"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary">Send message</button>
                    </div>
                </div>
            </div>
        </div>

</div>
@endif
@once
    @push('scripts')
        <script>


                document.onreadystatechange = () => {
                    // FIXME not sure why this needs wrapped in a document.ready in order to work. #exampleModal exists when the handler is added, but gets overwritten somewhere so the even handler is not on the element when using the buttons
                    if (document.readyState === 'complete') {
                        console.log('complete')
                        var exampleModal2 = document.getElementById('exampleModal')
                        console.log('example', exampleModal);
                        console.log('example2',exampleModal2);
                        console.log('both', exampleModal == exampleModal2);
                        console.log('loaded');
                        var exampleModal = document.getElementById('exampleModal')
                        console.log(exampleModal);
                        exampleModal.addEventListener('show.bs.modal', function (event) {
                            console.log('asdfasdfasdfasdfasdfasdf')
                            // Button that triggered the modal
                            var button = event.relatedTarget
                            // Extract info from data-bs-* attributes
                            var recipient = button.getAttribute('data-bs-whatever')
                            // If necessary, you could initiate an AJAX request here
                            // and then do the updating in a callback.
                            //
                            // Update the modal's content.
                            var modalTitle = exampleModal.querySelector('.modal-title')
                            var modalBodyInput = exampleModal.querySelector('.modal-body input')

                            modalTitle.textContent = 'New message to ' + recipient
                            modalBodyInput.value = recipient
                        })
                        exampleModal.addEventListener('show.bs.modal', () => {
                            console.log('show.bs.modal');
                        });
                        exampleModal.addEventListener('shown.bs.modal', () => {
                            console.log('shown.bs.modal');
                        });
                        exampleModal.addEventListener('hide.bs.modal 	', () => {
                            console.log('hide.bs.modal 	');
                        });
                        exampleModal.addEventListener('hidden.bs.modal', () => {
                            console.log('hidden.bs.modal');
                        });
                        exampleModal.addEventListener('hidePrevented.bs.modal', () => {
                            console.log('hidePrevented.bs.modal');
                        });
                    }
            };
        </script>
    @endpush
@endonce
