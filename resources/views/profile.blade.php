@extends('master')

@push('page-style')
<style>
    .profile-card {
        background: var(--surface);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        padding: 32px;
        text-align: center;
    }
    .profile-avatar-large {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: var(--primary);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 3rem;
        margin: 0 auto 20px;
        box-shadow: 0 8px 16px rgba(79, 70, 229, 0.2);
    }
    .form-section-title {
        font-size: 0.85rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--text-muted);
        margin-bottom: 20px;
        padding-bottom: 8px;
        border-bottom: 1px solid var(--border-color);
    }
    .btn-update {
        padding: 10px 24px;
        font-weight: 600;
    }
</style>
@endpush

@section('page-content')
<div class="mb-4">
    <h3 class="fw-bold mb-1 text-body">Profile Settings</h3>
    <p class="text-muted mb-0">Manage your account credentials and security details</p>
</div>

<div class="row g-4">
    <!-- Left Column: Profile Card -->
    <div class="col-lg-4">
        <div class="profile-card">
            <div class="profile-avatar-large">
                {{ substr(Auth::user()->name ?? 'A', 0, 1) }}
            </div>
            <h4 class="fw-bold text-body mb-1">{{ Auth::user()->name }}</h4>
            <p class="text-muted mb-4">Manager</p>
            
            <div class="text-start border-top pt-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="avatar bg-light text-primary" style="width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="fa-regular fa-envelope"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Email Address</small>
                        <span class="fw-semibold text-body" style="word-break: break-all;">{{ Auth::user()->email }}</span>
                    </div>
                </div>
                
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar bg-light text-primary" style="width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="fa-regular fa-calendar-days"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Account Created</small>
                        <span class="fw-semibold text-body">{{ Auth::user()->created_at ? Auth::user()->created_at->format('M d, Y') : 'N/A' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Settings Form -->
    <div class="col-lg-8">
        <div class="card border-0">
            <div class="card-body p-4">
                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Profile Details -->
                    <div class="form-section-title">
                        <i class="fa-regular fa-address-card me-2"></i> Personal Information
                    </div>
                    
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-body">Full Name</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', Auth::user()->name) }}" required placeholder="Enter full name">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-body">Email Address</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', Auth::user()->email) }}" required placeholder="Enter email address">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Security Details -->
                    <div class="form-section-title">
                        <i class="fa-solid fa-shield-halved me-2"></i> Change Password
                    </div>
                    
                    <p class="text-muted mb-3" style="font-size: 0.85rem;">Leave password fields blank if you do not wish to change your password.</p>

                    <div class="row g-3 mb-4">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold text-body">Current Password</label>
                            <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" placeholder="Enter current password to verify identity">
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-body">New Password</label>
                            <input type="password" name="new_password" class="form-control @error('new_password') is-invalid @enderror" placeholder="Enter new password (min. 8 characters)">
                            @error('new_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-body">Confirm New Password</label>
                            <input type="password" name="new_password_confirmation" class="form-control" placeholder="Re-enter new password">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary btn-update d-flex align-items-center gap-2">
                            <i class="fa-regular fa-floppy-disk"></i> Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
