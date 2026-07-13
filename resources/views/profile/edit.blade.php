@extends('master')

@section('page-content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0  fw-bold">My Profile</h1>
            <p class="text-muted mb-0">Manage your profile details, password, and profile image.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <h6 class="fw-bold mb-2"><i class="fa-solid fa-triangle-exclamation me-2"></i> Update Failed:</h6>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <!-- Left Side: Profile Preview Card -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm text-center h-100">
                <div class="card-body d-flex flex-column align-items-center justify-content-center py-5">
                    <div class="position-relative mb-4">
                        <div class="avatar-container" style="width: 140px; height: 140px; border-radius: 50%; overflow: hidden; background: #eef2f6; border: 4px solid var(--primary); display: flex; align-items: center; justify-content: center; font-size: 3.5rem; font-weight: bold; color: var(--primary);">
                            @if($user->profile_image)
                                <img id="profilePreview" src="{{ asset('storage/' . $user->profile_image) }}" alt="Profile Image" style="width: 100%; height: 100%; object-fit: cover;">
                            @else
                                <span id="profileLetter">{{ substr($user->name ?? 'A', 0, 1) }}</span>
                                <img id="profilePreview" src="#" alt="Profile Image" style="width: 100%; height: 100%; object-fit: cover; display: none;">
                            @endif
                        </div>
                    </div>
                    <h4 class="fw-bold mb-1">{{ $user->name }}</h4>
                    <p class="text-muted mb-3">{{ $user->email }}</p>
                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-semibold text-capitalize">
                        <i class="fa-solid fa-{{ $user->role === 'admin' ? 'user-shield' : 'user' }} me-1"></i> {{ $user->role }} Account
                    </span>
                </div>
            </div>
        </div>

        <!-- Right Side: Details Edit Form -->
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header border-bottom py-3">
                    <h5 class="mb-0 fw-bold"><i class="fa-solid fa-user-pen text-primary me-2"></i> Update Profile Details</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label text-muted fw-bold small text-uppercase">Full Name</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fa-regular fa-user text-muted"></i></span>
                                    <input type="text" name="name" id="name" class="form-control border-start-0 ps-0" value="{{ old('name', $user->name) }}" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label text-muted fw-bold small text-uppercase">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fa-regular fa-envelope text-muted"></i></span>
                                    <input type="email" name="email" id="email" class="form-control border-start-0 ps-0" value="{{ old('email', $user->email) }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="profile_image" class="form-label text-muted fw-bold small text-uppercase">Profile Image</label>
                            <input type="file" name="profile_image" id="profile_image" class="form-control" accept="image/*">
                            <div class="form-text">Choose a square image up to 2MB (formats: JPG, PNG, WEBP).</div>
                        </div>

                        <hr class="my-4">
                        <h6 class="fw-bold mb-3 text-secondary"><i class="fa-solid fa-lock me-1"></i> Change Password (Leave blank to keep current)</h6>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label text-muted fw-bold small text-uppercase">New Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-key text-muted"></i></span>
                                    <input type="password" name="password" id="password" class="form-control border-start-0 ps-0" placeholder="Minimum 8 characters">
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label text-muted fw-bold small text-uppercase">Confirm New Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-lock text-muted"></i></span>
                                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control border-start-0 ps-0" placeholder="Re-type password">
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-primary px-4 fw-semibold py-2">
                                <i class="fa-solid fa-floppy-disk me-1"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('page-script')
<script>
    document.getElementById('profile_image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                const preview = document.getElementById('profilePreview');
                const letter = document.getElementById('profileLetter');
                
                preview.src = event.target.result;
                preview.style.display = 'block';
                if (letter) {
                    letter.style.display = 'none';
                }
            };
            reader.readAsDataURL(file);
        }
    });
</script>
@endpush