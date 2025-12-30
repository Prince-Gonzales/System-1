@extends('layouts.base', ['title' => 'Profile', 'assets' => ['resources/css/profile.css', 'resources/css/globals.css', 'resources/js/app.js']])

@php
    use App\Helpers\ProfilePictureHelper;
    $success = session('success') ?? [];
@endphp

@section('content')
    <div class="navbar">
        <a href="{{ route('home') }}">
            <div class="brand">Home</div>
        </a>
        <div class="profile" id="profileLink">
            @if ($profilePictureUrl = ProfilePictureHelper::getProfilePictureUrl($user->profile_picture))
                <img src="{{ $profilePictureUrl }}" alt="Profile Picture">
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
    <div class="container">
        <h1>Profile Settings</h1>

        <div class="profile-picture-section">
            <h2>Profile Picture</h2>
            <div class="profile-picture-container">
                @if ($profilePictureUrl = ProfilePictureHelper::getProfilePictureUrl($user->profile_picture))
                    <img src="{{ $profilePictureUrl }}" alt="Profile Picture"
                         class="profile-picture" id="currentProfilePicture" onclick="openImageModal('{{ $profilePictureUrl }}')">
                @else
                    <div class="profile-picture default-avatar" id="currentProfilePicture">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                @endif
            </div>
            
            <!-- Image Preview Modal -->
            <div id="imagePreviewModal" class="image-preview-modal" onclick="closeImageModal()" style="display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;background-color:rgba(0,0,0,0.8);align-items:center;justify-content:center;">
                <div class="modal-content" onclick="event.stopPropagation()" style="max-width:400px;max-height:400px;width:auto;height:auto;position:relative;background-color:white;padding:15px;border-radius:8px;box-shadow:0 5px 15px rgba(0,0,0,0.3);">
                    <img id="modalImage" src="" alt="" style="max-width:100%;max-height:100%;width:auto;height:auto;object-fit:contain;border-radius:4px;display:block;">
                </div>
                <span class="modal-close" onclick="closeImageModal()" style="position:absolute;top:15px;right:25px;color:#f1f1f1;font-size:30px;font-weight:bold;cursor:pointer;background:rgba(0,0,0,0.6);width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;border:2px solid rgba(255,255,255,0.4);">&times;</span>
            </div>
            <form id="profilePictureForm" action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="profile-upload-form">
                @csrf
                <div class="form-group">
                    <label for="profile_picture">Change Profile Picture</label>
                    <input type="file" name="profile_picture" id="profile_picture" accept="image/png, image/jpeg, image/jpg, image/gif, image/webp">
                    <small>Image files only (PNG, JPEG, JPG, GIF, WEBP). Max size: 30MB</small>
                </div>
                
                <div id="uploadProgress" class="progress-container">
                    <div id="progressBar" class="progress-bar"></div>
                </div>
                <div id="uploadStatus" class="upload-status"></div>

                <button name="update-profile-picture" type="submit" class="btn" id="uploadButton">Upload Picture</button>
            </form>
            @error('profile_picture')
                <div class="error-messages">
                    <p class="error">{{ $message }}</p>
                </div>
            @enderror
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const profilePictureInput = document.getElementById('profile_picture');
                const currentProfilePicture = document.getElementById('currentProfilePicture');
                const profilePictureForm = document.getElementById('profilePictureForm');
                
                // Store the original profile picture source to allow reverting
                let originalProfilePictureSrc = null;
                if (currentProfilePicture.tagName === 'IMG') {
                    originalProfilePictureSrc = currentProfilePicture.src;
                } else {
                    originalProfilePictureSrc = currentProfilePicture.style.backgroundImage || null;
                }

                profilePictureInput.addEventListener('change', function(event) {
                    const file = event.target.files[0];
                    if (file) {
                        // Validation for preview
                        const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
                        if (!validTypes.includes(file.type)) {
                            showStatus('Invalid file type for preview.', 'error');
                            return;
                        }

                        const reader = new FileReader();
                        reader.onload = function(e) {
                            updateProfileImageDOM(e.target.result);
                        };
                        reader.readAsDataURL(file);
                    } else {
                        // Revert to original profile picture when file selection is cancelled
                        if (originalProfilePictureSrc) {
                            updateProfileImageDOM(originalProfilePictureSrc);
                        }
                    }
                });

                profilePictureForm.addEventListener('submit', function(event) {
                    event.preventDefault();
                    uploadProfilePicture();
                });
            });

            function updateProfileImageDOM(src) {
                let currentProfilePicture = document.getElementById('currentProfilePicture');
                if (currentProfilePicture.tagName === 'IMG') {
                    currentProfilePicture.src = src;
                    currentProfilePicture.setAttribute('onclick', `openImageModal('${src}')`);
                } else {
                    const img = document.createElement('img');
                    img.src = src;
                    img.alt = 'Profile Picture';
                    img.className = 'profile-picture';
                    img.id = 'currentProfilePicture';
                    img.setAttribute('onclick', `openImageModal('${src}')`);
                    currentProfilePicture.parentNode.replaceChild(img, currentProfilePicture);
                }
            }

            function uploadProfilePicture() {
                const input = document.getElementById('profile_picture');
                const file = input.files[0];
                
                if (!file) {
                    showStatus('Please select a file first.', 'error');
                    return;
                }

                // Client-side validation
                const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
                if (!validTypes.includes(file.type)) {
                    showStatus('Invalid file type. Please select an image.', 'error');
                    return;
                }

                const maxSize = 30 * 1024 * 1024; // 30MB
                if (file.size > maxSize) {
                    showStatus('File size exceeds 30MB limit.', 'error');
                    return;
                }

                const formData = new FormData(document.getElementById('profilePictureForm'));
                formData.append('update-profile-picture', '1'); 

                const xhr = new XMLHttpRequest();
                const progressBar = document.getElementById('progressBar');
                const progressContainer = document.getElementById('uploadProgress');
                const uploadButton = document.getElementById('uploadButton');

                progressContainer.style.display = 'block';
                uploadButton.disabled = true;
                showStatus('Uploading...', 'info');

                xhr.open('POST', '{{ route('profile.update') }}', true);
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                xhr.setRequestHeader('Accept', 'application/json');

                xhr.upload.onprogress = function(e) {
                    if (e.lengthComputable) {
                        const percentComplete = (e.loaded / e.total) * 100;
                        progressBar.style.width = percentComplete + '%';
                    }
                };

                xhr.onload = function() {
                    uploadButton.disabled = false;
                    progressContainer.style.display = 'none';
                    progressBar.style.width = '0%';

                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            showStatus(response.message, 'success');
                            
                            if (response.profile_picture_url) {
                                // Add cache busting and update DOM when image loads
                                const newUrl = response.profile_picture_url + (response.profile_picture_url.includes('?') ? '&' : '?') + 't=' + Date.now();
                                const imgTest = new Image();
                                imgTest.onload = function() {
                                    updateProfileImageDOM(newUrl);
                                    const navImg = document.querySelector('.profile img');
                                    if (navImg) {
                                        navImg.src = newUrl;
                                    } else {
                                        const navAvatar = document.querySelector('.header-avatar');
                                        if (navAvatar) {
                                            const newNavImg = document.createElement('img');
                                            newNavImg.src = newUrl;
                                            newNavImg.alt = 'Profile Picture';
                                            navAvatar.parentNode.replaceChild(newNavImg, navAvatar);
                                        }
                                    }
                                };
                                imgTest.onerror = function() {
                                    // Fallback: hard reload to reflect new asset
                                    window.location.reload();
                                };
                                imgTest.src = newUrl;
                            }
                        } catch (e) {
                            showStatus('Upload successful, but failed to parse response.', 'success');
                        }
                    } else {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            showStatus(response.message || 'Upload failed.', 'error');
                        } catch (e) {
                            showStatus('An error occurred during upload (' + xhr.status + ').', 'error');
                        }
                    }
                };

                xhr.onerror = function() {
                    uploadButton.disabled = false;
                    progressContainer.style.display = 'none';
                    showStatus('Network error. Please check your connection.', 'error');
                };

                xhr.send(formData);
            }

            function showStatus(message, type) {
                const statusDiv = document.getElementById('uploadStatus');
                statusDiv.textContent = message;
                statusDiv.style.color = type === 'error' ? 'red' : (type === 'success' ? 'green' : '#555');
            }

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

        @if (is_array($success) && array_key_exists('update-profile', $success))
            <div class="success-messages">
                <p class="success">{{ $success['update-profile'] }}</p>
            </div>
        @elseif (is_array($success) && array_key_exists('update-password', $success))
            <div class="success-messages">
                <p class="success">{{ $success['update-password'] }}</p>
            </div>
        @elseif ($errors->has('update-profile'))
            <div class="error-messages">
                <p class="error">{{ $errors->first('update-profile') }}</p>
            </div>
        @elseif ($errors->has('update-password'))
            <div class="error-messages">
                <p class="error">{{ $errors->first('update-password') }}</p>
            </div>
        @endif

        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}">
                @error('name')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input id="email" name="email" value="{{ $user->email }}" disabled>
            </div>
            <button name="update-profile" type="submit" class="btn">Update Profile</button>
        </form>
    </div>

    <div class="container">
        <h2>Change Password</h2>
        <form action="{{ route('profile.update') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="current-password">Current Password</label>
                <input type="password" name="current-password" id="current-password" required>
                @error('current-password')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>
            <div class="form-group">
                <label for="new-password">New Password</label>
                <input type="password" name="password" id="new-password" required>
                @error('password')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>
            <div class="form-group">
                <label for="confirm-password">Confirm New Password</label>
                <input type="password" name="password_confirmation" id="confirm-password" required>
            </div>
            <button name="update-password" type="submit" class="btn">Update Password</button>
        </form>

    </div>

    <div class="container">
        <h2>Delete Account</h2>
        <form action="{{ route('profile.update') }}" method="POST">
            @csrf
            <button name="delete" class="btn btn-danger">Delete Account</button>
        </form>
    </div>
@endsection

