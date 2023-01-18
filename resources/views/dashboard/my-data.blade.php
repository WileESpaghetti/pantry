<div class="card mt-3">
    <div class="card-header">{{ __('My Data') }}</div>

    <div class="card-body">
        <ul class="nav text-center">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('folders.index') }}">
                    <i class="fas fa-folder d-block fs-1"></i>
                    {{ __('Folders') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('tags.index') }}">
                    <i class="fas fa-tag d-block fs-1"></i>
                    {{ __('Tags') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('bookmarks.index') }}">
                    <i class="fas fa-bookmark d-block fs-1"></i>
                    {{ __('Bookmarks') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('user-settings') }}">
                    <i class="fas fa-user-cog d-block fs-1"></i>
                    {{ __('Settings') }}
                </a>
            </li>
        </ul>
    </div>
</div>
