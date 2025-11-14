@extends('master.userlayout')

@section('content')
<style>
    .content-wrapper {
        background: #f8f9fa;
        min-height: auto;
        padding-top: 1rem;
        padding: 2rem 0;
        display: flex;
        align-items: center;
    }

    .main-container {
        width: 100%;
    }

    .password-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        padding: 1.5rem 2rem;
        margin: auto 0;
        width: 100%;
    }

    .security-header {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #f1f3f5;
    }

    .security-icon {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .security-icon i {
        font-size: 1.5rem;
        color: white;
    }

    .security-header-text {
        flex: 1;
        text-align: left;
    }

    .security-header h2 {
        font-size: 1.35rem;
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 0.25rem;
    }

    .security-header p {
        color: #718096;
        font-size: 0.85rem;
        margin: 0;
    }

    .form-group {
        margin-bottom: 1rem;
    }

    .form-label {
        font-weight: 600;
        color: #4a5568;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .form-label i {
        font-size: 1rem;
        color: #667eea;
    }

    .input-wrapper {
        position: relative;
    }

    .form-control {
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 0.5rem 2.5rem 0.5rem 0.875rem;
        font-size: 0.875rem;
        transition: all 0.2s ease;
        background: #fff;
    }

    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        outline: none;
    }

    .form-control::placeholder {
        color: #a0aec0;
    }

    .password-toggle {
        position: absolute;
        right: 0;
        top: 0;
        height: 100%;
        background: none;
        border: none;
        padding: 0 1rem;
        color: #718096;
        cursor: pointer;
        transition: color 0.2s ease;
    }

    .password-toggle:hover {
        color: #667eea;
    }

    .password-toggle:focus {
        outline: none;
    }

    .strength-indicator {
        height: 4px;
        border-radius: 2px;
        margin-top: 0.5rem;
        background: #e2e8f0;
        overflow: hidden;
    }

    .strength-bar {
        height: 100%;
        transition: all 0.3s ease;
        border-radius: 2px;
    }

    .strength-weak { background: #f56565; width: 25%; }
    .strength-medium { background: #ed8936; width: 50%; }
    .strength-good { background: #38b2ac; width: 75%; }
    .strength-strong { background: #48bb78; width: 100%; }

    .form-text {
        font-size: 0.85rem;
        margin-top: 0.4rem;
        display: block;
    }

    .text-muted { color: #718096; }
    .text-danger { color: #f56565; }
    .text-warning { color: #ed8936; }
    .text-info { color: #38b2ac; }
    .text-success { color: #48bb78; }

    .password-tips {
        background: #f7fafc;
        border: 1px solid #e2e8f0;
        border-left: 3px solid #38b2ac;
        border-radius: 6px;
        padding: 0.875rem 1rem;
        margin-top: 0;
        height: 100%;
    }

    .password-tips h6 {
        color: #2d3748;
        font-weight: 600;
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.85rem;
    }

    .password-tips h6 i {
        color: #38b2ac;
        font-size: 1rem;
    }

    .password-tips ul {
        margin-bottom: 0;
        padding-left: 0;
        list-style: none;
    }

    .password-tips li {
        color: #4a5568;
        margin-bottom: 0.5rem;
        font-size: 0.8rem;
        padding-left: 1.25rem;
        position: relative;
        line-height: 1.4;
    }

    .password-tips li:last-child {
        margin-bottom: 0;
    }

    .password-tips li:before {
        content: "✓";
        position: absolute;
        left: 0;
        color: #38b2ac;
        font-weight: 600;
    }

    .alert {
        border: none;
        border-radius: 6px;
        padding: 0.875rem 1rem;
        margin-bottom: 1.5rem;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .alert-success {
        background: #f0fdf4;
        color: #166534;
        border-left: 3px solid #48bb78;
    }

    .alert-danger {
        background: #fef2f2;
        color: #991b1b;
        border-left: 3px solid #f56565;
    }

    .alert i {
        font-size: 1.1rem;
    }

    .btn-group {
        display: flex;
        gap: 0.75rem;
        justify-content: center;
        margin-top: 1.25rem;
        padding-top: 1rem;
        border-top: 1px solid #f1f3f5;
    }

    .btn {
        padding: 0.55rem 1.5rem;
        border-radius: 6px;
        font-weight: 500;
        font-size: 0.875rem;
        transition: all 0.2s ease;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        text-decoration: none;
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        box-shadow: 0 2px 4px rgba(102, 126, 234, 0.3);
    }

    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(102, 126, 234, 0.4);
        color: white;
    }

    .btn-secondary {
        background: #f7fafc;
        color: #4a5568;
        border: 1px solid #e2e8f0;
    }

    .btn-secondary:hover {
        background: #edf2f7;
        color: #2d3748;
    }

    .btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .loading-spinner {
        display: none;
        width: 14px;
        height: 14px;
        border: 2px solid #ffffff;
        border-top: 2px solid transparent;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .is-invalid {
        border-color: #f56565 !important;
    }

    .is-invalid:focus {
        box-shadow: 0 0 0 3px rgba(245, 101, 101, 0.1) !important;
    }

    @media (max-width: 768px) {
        .password-card {
            padding: 1.5rem;
        }

        .btn-group {
            flex-direction: column;
        }

        .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<div class="content-wrapper">
    <!-- Main Content -->
    <div class="main-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10 col-xl-9">
                    <div class="password-card">
                        <div class="security-header">
                            <div class="security-icon">
                                <i class="mdi mdi-shield-lock"></i>
                            </div>
                            <div class="security-header-text">
                                <h2>Change Password</h2>
                                <p>Keep your account secure with a strong password</p>
                            </div>
                        </div>

                        @if(session('success'))
                            <div class="alert alert-success" role="alert">
                                <i class="mdi mdi-check-circle"></i>
                                <span>{{ session('success') }}</span>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger" role="alert">
                                <i class="mdi mdi-alert-circle"></i>
                                <span>{{ session('error') }}</span>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('lecturer.change_password') }}" id="passwordForm">
                            @csrf

                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label for="current_password" class="form-label">
                                            <i class="mdi mdi-lock-outline"></i>
                                            Current Password
                                        </label>
                                        <div class="input-wrapper">
                                            <input type="password"
                                                   class="form-control @error('current_password') is-invalid @enderror"
                                                   id="current_password"
                                                   name="current_password"
                                                   placeholder="Enter your current password"
                                                   required>
                                            <button class="password-toggle" type="button" onclick="togglePassword('current_password')">
                                                <i class="mdi mdi-eye" id="current_password_icon"></i>
                                            </button>
                                        </div>
                                        @error('current_password')
                                            <small class="text-danger form-text">
                                                <i class="mdi mdi-alert-circle-outline"></i> {{ $message }}
                                            </small>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="new_password" class="form-label">
                                            <i class="mdi mdi-lock-plus-outline"></i>
                                            New Password
                                        </label>
                                        <div class="input-wrapper">
                                            <input type="password"
                                                   class="form-control @error('new_password') is-invalid @enderror"
                                                   id="new_password"
                                                   name="new_password"
                                                   placeholder="Create a strong new password"
                                                   required
                                                   oninput="checkPasswordStrength()">
                                            <button class="password-toggle" type="button" onclick="togglePassword('new_password')">
                                                <i class="mdi mdi-eye" id="new_password_icon"></i>
                                            </button>
                                        </div>
                                        <div class="strength-indicator">
                                            <div class="strength-bar" id="strengthBar"></div>
                                        </div>
                                        <small class="form-text text-muted" id="strengthText">
                                            Password strength will appear here
                                        </small>
                                        @error('new_password')
                                            <small class="text-danger form-text">
                                                <i class="mdi mdi-alert-circle-outline"></i> {{ $message }}
                                            </small>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="new_password_confirmation" class="form-label">
                                            <i class="mdi mdi-lock-check-outline"></i>
                                            Confirm New Password
                                        </label>
                                        <div class="input-wrapper">
                                            <input type="password"
                                                   class="form-control"
                                                   id="new_password_confirmation"
                                                   name="new_password_confirmation"
                                                   placeholder="Confirm your new password"
                                                   required
                                                   oninput="checkPasswordMatch()">
                                            <button class="password-toggle" type="button" onclick="togglePassword('new_password_confirmation')">
                                                <i class="mdi mdi-eye" id="new_password_confirmation_icon"></i>
                                            </button>
                                        </div>
                                        <small class="form-text" id="matchText"></small>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="password-tips">
                                        <h6>
                                            <i class="mdi mdi-lightbulb-outline"></i>
                                            Password Security Tips
                                        </h6>
                                        <ul>
                                            <li>Use at least 8 characters</li>
                                            <li>Include uppercase and lowercase letters</li>
                                            <li>Add numbers and special characters</li>
                                            <li>Avoid personal information</li>
                                            <li>Don't reuse old passwords</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="btn-group">
                                <button type="submit" class="btn btn-primary">
                                    <div class="loading-spinner" id="loadingSpinner"></div>
                                    <i class="mdi mdi-shield-check" id="submitIcon"></i>
                                    Update Password
                                </button>
                                <a href="{{ route('lecturer.courses') }}" class="btn btn-secondary">
                                    <i class="mdi mdi-arrow-left"></i>
                                    Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(fieldId) {
    const passwordField = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '_icon');

    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        icon.classList.remove('mdi-eye');
        icon.classList.add('mdi-eye-off');
    } else {
        passwordField.type = 'password';
        icon.classList.remove('mdi-eye-off');
        icon.classList.add('mdi-eye');
    }
}

function checkPasswordStrength() {
    const password = document.getElementById('new_password').value;
    const strengthBar = document.getElementById('strengthBar');
    const strengthText = document.getElementById('strengthText');

    let strength = 0;
    let text = '';

    // Length check
    if (password.length >= 8) strength += 1;
    if (password.length >= 12) strength += 1;

    // Character variety checks
    if (/[a-z]/.test(password)) strength += 1;
    if (/[A-Z]/.test(password)) strength += 1;
    if (/[0-9]/.test(password)) strength += 1;
    if (/[^A-Za-z0-9]/.test(password)) strength += 1;

    // Remove all strength classes
    strengthBar.className = 'strength-bar';

    if (password.length === 0) {
        text = 'Password strength will appear here';
        strengthText.className = 'form-text text-muted';
    } else if (strength <= 2) {
        strengthBar.classList.add('strength-weak');
        text = 'Weak - Add more variety';
        strengthText.className = 'form-text text-danger';
    } else if (strength <= 4) {
        strengthBar.classList.add('strength-medium');
        text = 'Medium - Getting better';
        strengthText.className = 'form-text text-warning';
    } else if (strength <= 5) {
        strengthBar.classList.add('strength-good');
        text = 'Good - Almost there';
        strengthText.className = 'form-text text-info';
    } else {
        strengthBar.classList.add('strength-strong');
        text = 'Strong - Excellent password!';
        strengthText.className = 'form-text text-success';
    }

    strengthText.innerHTML = text;
    checkPasswordMatch();
}

function checkPasswordMatch() {
    const password = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('new_password_confirmation').value;
    const matchText = document.getElementById('matchText');

    if (confirmPassword.length === 0) {
        matchText.innerHTML = '';
        return;
    }

    if (password === confirmPassword) {
        matchText.innerHTML = '<i class="mdi mdi-check-circle"></i> <span class="text-success">Passwords match!</span>';
    } else {
        matchText.innerHTML = '<i class="mdi mdi-close-circle"></i> <span class="text-danger">Passwords do not match</span>';
    }
}

// Form submission with loading state
document.getElementById('passwordForm').addEventListener('submit', function(e) {
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const spinner = document.getElementById('loadingSpinner');
    const icon = document.getElementById('submitIcon');

    submitBtn.disabled = true;
    spinner.style.display = 'inline-block';
    icon.style.display = 'none';
});

// Auto-hide alerts after 5 seconds
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        alert.style.transition = 'opacity 0.5s ease';
        alert.style.opacity = '0';
        setTimeout(function() {
            alert.remove();
        }, 500);
    });
}, 5000);
</script>
@endsection
