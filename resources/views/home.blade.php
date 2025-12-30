@extends('layouts.base', ['title' => 'Home', 'assets' => ['resources/css/styles.css', 'resources/css/globals.css', 'resources/css/profile.css', 'resources/js/app.js']])

@php
    use App\Helpers\ProfilePictureHelper;
@endphp

@section('content')
    <div class="navbar">
        <div class="brand">Php Project</div>
        <div class="profile" id="profileLink">
            @if ($profilePictureUrl = ProfilePictureHelper::getProfilePictureUrl($user->profile_picture))
                <img src="{{ $profilePictureUrl }}" alt="Profile Picture" onclick="openImageModal('{{ $profilePictureUrl }}')">
            @else
                <div class="header-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
            @endif
            <span>{{ $user->name }}</span>

            <div class="dropdown">
                <a href="{{ route('profile') }}">Profile</a>
                <a href="{{ route('logout') }}">Logout</a>
            </div>
        </div>
    </div>
    
    <!-- Image Preview Modal -->
    <div id="imagePreviewModal" class="image-preview-modal" onclick="closeImageModal()" style="display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;background-color:rgba(0,0,0,0.8);align-items:center;justify-content:center;">
        <div class="modal-content" onclick="event.stopPropagation()" style="max-width:400px;max-height:400px;width:auto;height:auto;position:relative;background-color:white;padding:15px;border-radius:8px;box-shadow:0 5px 15px rgba(0,0,0,0.3);">
            <img id="modalImage" src="" alt="" style="max-width:100%;max-height:100%;width:auto;height:auto;object-fit:contain;border-radius:4px;display:block;">
        </div>
        <span class="modal-close" onclick="closeImageModal()" style="position:absolute;top:15px;right:25px;color:#f1f1f1;font-size:30px;font-weight:bold;cursor:pointer;background:rgba(0,0,0,0.6);width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;border:2px solid rgba(255,255,255,0.4);">&times;</span>
    </div>
    
    <div class="container">
        <h1>Welcome to PHP Project</h1>
        <p>Your application Homepage.</p>
    </div>
    
    <script>
        // Modal functions for full image preview
        function openImageModal(imageSrc) {
            const modal = document.getElementById('imagePreviewModal');
            const modalImg = document.getElementById('modalImage');
            
            modalImg.src = imageSrc;
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden'; // Prevent background scrolling
        }

        function closeImageModal() {
            const modal = document.getElementById('imagePreviewModal');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto'; // Restore scrolling
        }

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeImageModal();
            }
        });
    </script>
@endsection

