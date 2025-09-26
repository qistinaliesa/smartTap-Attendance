@extends('master.userlayout')

@section('content')
<style>
    .password-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 15px;
        box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        overflow: hidden;
        position: relative;
        margin-bottom: 1rem;
    }

    .password-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #00b09b, #96c93d);
    }

    .card-header-custom {
        background: rgba(255,255,255,0.95);
        backdrop-filter: blur(10px);
        border: none;
        padding: 1.5rem 1.5rem 1rem 1.5rem;
        text-align: center;
        position: relative;
    }

    .security-icon {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 0.75rem;
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
    }

    .security-icon i {
        font-size: 1.8rem;
        color: white;
    }

    .card-title-custom {
        font-size: 1.5rem;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 0.25rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .card-subtitle-custom {
        color: #718096;
        font-size: 0.9rem;
        margin-bottom: 0;
    }

    .form-section {
        background: white;
        padding: 1.5rem;
        margin: 0;
    }

    .form-group-enhanced {
        margin-bottom: 1.25rem;
        position: relative;
    }

    .form-label-custom {
        font-weight: 600;
        color: #4a5568;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .input-group-enhanced {
        position: relative;
    }

    .form-control-enhanced {
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        padding: 0.75rem 1rem;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        background: #f8fafc;
        height: auto;
    }

    .form-control-enhanced:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.1);
        background: white;
        outline: none;
    }

    .input-group-enhanced .input-group-append {
        position: absolute;
        right: 0;
        top: 0;
        height: 100%;
        z-index: 10;
    }

    .password-toggle {
        background: none;
        border: none;
        padding: 0 1rem;
        height: 100%;
        color: #718096;
        cursor: pointer;
        transition: color 0.3s ease;
        border-radius: 0 8px 8px 0;
    }

    .password-toggle:hover {
        color: #667eea;
    }

    .password-toggle:focus {
        outline: none;
    }

    .strength-indicator {
        height: 3px;
        border-radius: 2px;
        margin-top: 0.4rem;
        background: #e2e8f0;
        overflow: hidden;
        transition: all 0.3s ease;
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

    .alert-enhanced {
        border: none;
        border-radius: 10px;
        padding: 0.75rem 1rem;
        margin-bottom: 1rem;
        font-weight: 500;
        font-size: 0.9rem;
        position: relative;
        overflow: hidden;
    }

    .alert-success-enhanced {
        background: linear-gradient(135deg, #48bb78, #38a169);
        color: white;
        box-shadow: 0 4px 12px rgba(72, 187, 120, 0.3);
    }

    .alert-danger-enhanced {
        background: linear-gradient(135deg, #f56565, #e53e3e);
        color: white;
        box-shadow: 0 4px 12px rgba(245, 101, 101, 0.3);
    }

    .btn-enhanced {
        padding: 0.7rem 1.5rem;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
    }

    .btn-primary-enhanced {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    }

    .btn-primary-enhanced:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        color: white;
    }

    .btn-secondary-enhanced {
        background: #f7fafc;
        color: #4a5568;
        border: 2px solid #e2e8f0;
    }

    .btn-secondary-enhanced:hover {
        background: #edf2f7;
        transform: translateY(-1px);
        color: #2d3748;
    }

    .password-tips {
        background: linear-gradient(135deg, #e6fffa, #b2f5ea);
        border-radius: 10px;
        padding: 1rem;
        margin-top: 1rem;
        border-left: 3px solid #38b2ac;
    }

    .password-tips h6 {
        color: #234e52;
        font-weight: 700;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.9rem;
    }

    .password-tips ul {
        margin-bottom: 0;
        padding-left: 1rem;
    }

    .password-tips li {
        color: #2c7a7b;
        margin-bottom: 0.25rem;
        font-size: 0.8rem;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .password-card {
        animation: slideIn 0.6s ease-out;
    }

    .loading-spinner {
        display: none;
        width: 16px;
        height: 16px;
        border: 2px solid #ffffff;
        border-top: 2px solid transparent;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .content-wrapper {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        min-height: 100vh;
        padding: 1rem 0;
        display: flex;
        align-items: center;
    }

    .container {
        width: 100%;
    }

    /* Responsive adjustments */
    @media (max-height: 800px) {
        .form-section {
            padding: 1rem;
        }

        .form-group-enhanced {
            margin-bottom: 1rem;
        }

        .password-tips {
            margin-top: 0.75rem;
            padding: 0.75rem;
        }

        .card-header-custom {
            padding: 1rem;
        }
    }

    @media (max-height: 700px) {
        .security-icon {
            width: 50px;
            height: 50px;
            margin-bottom: 0.5rem;
        }

        .security-icon i {
            font-size: 1.5rem;
        }

        .card-title-custom {
            font-size: 1.3rem;
        }

        .form-group-enhanced {
            margin-bottom: 0.75rem;
        }
    }

    /* Small text utilities */
    .form-text {
        font-size: 0.8rem !important;
        margin-top: 0.3rem;
    }

    small.text-danger, small.text-success, small.text-warning, small.text-info {
        font-size: 0.8rem;
    }
</style>

<div class="content-wrapper">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="card password-card">
                    <div class="card-header-custom">
                        <div class="security-icon">
                            <i class="mdi mdi-shield-lock"></i>
                        </div>
                        <h2 class="card-title-custom">Change Password</h2>
                        <p class="card-subtitle-custom">Keep your account secure with a strong password</p>
                    </div>

                    <div class="form-section">
                        @if(session('success'))
                            <div class="alert alert-success-enhanced alert-enhanced" role="alert">
                                <i class="mdi mdi-check-circle mr-2"></i>
                                {{ session('success') }}
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger-enhanced alert-enhanced" role="alert">
                                <i class="mdi mdi-alert-circle mr-2"></i>
                                {{ session('error') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('lecturer.change_password') }}" id="passwordForm">
                            @csrf

                            <div class="form-group-enhanced">
                                <label for="current_password" class="form-label-custom">
                                    <i class="mdi mdi-lock-outline"></i>
                                    Current Password
                                </label>
                                <div class="input-group-enhanced">
                                    <input type="password"
                                           class="form-control form-control-enhanced @error('current_password') is-invalid @enderror"
                                           id="current_password"
                                           name="current_password"
                                           placeholder="Enter your current password"
                                           required>
                                    <div class="input-group-append">
                                        <button class="password-toggle" type="button" onclick="togglePassword('current_password')">
                                            <i class="mdi mdi-eye" id="current_password_icon"></i>
                                        </button>
                                    </div>
                                </div>
                                @error('current_password')
                                    <small class="text-danger mt-2 d-block">
                                        <i class="mdi mdi-alert-circle-outline mr-1"></i>{{ $message }}
                                    </small>
                                @enderror
                            </div>

                            <div class="form-group-enhanced">
                                <label for="new_password" class="form-label-custom">
                                    <i class="mdi mdi-lock-plus-outline"></i>
                                    New Password
                                </label>
                                <div class="input-group-enhanced">
                                    <input type="password"
                                           class="form-control form-control-enhanced @error('new_password') is-invalid @enderror"
                                           id="new_password"
                                           name="new_password"
                                           placeholder="Create a strong new password"
                                           required
                                           oninput="checkPasswordStrength()">
                                    <div class="input-group-append">
                                        <button class="password-toggle" type="button" onclick="togglePassword('new_password')">
                                            <i class="mdi mdi-eye" id="new_password_icon"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="strength-indicator">
                                    <div class="strength-bar" id="strengthBar"></div>
                                </div>
                                <small class="form-text text-muted mt-2" id="strengthText">
                                    Password strength will appear here
                                </small>
                                @error('new_password')
                                    <small class="text-danger mt-2 d-block">
                                        <i class="mdi mdi-alert-circle-outline mr-1"></i>{{ $message }}
                                    </small>
                                @enderror
                            </div>

                            <div class="form-group-enhanced">
                                <label for="new_password_confirmation" class="form-label-custom">
                                    <i class="mdi mdi-lock-check-outline"></i>
                                    Confirm New Password
                                </label>
                                <div class="input-group-enhanced">
                                    <input type="password"
                                           class="form-control form-control-enhanced"
                                           id="new_password_confirmation"
                                           name="new_password_confirmation"
                                           placeholder="Confirm your new password"
                                           required
                                           oninput="checkPasswordMatch()">
                                    <div class="input-group-append">
                                        <button class="password-toggle" type="button" onclick="togglePassword('new_password_confirmation')">
                                            <i class="mdi mdi-eye" id="new_password_confirmation_icon"></i>
                                        </button>
                                    </div>
                                </div>
                                <small class="form-text" id="matchText"></small>
                            </div>

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

                            <div class="text-center mt-3">
                                <button type="submit" class="btn btn-primary-enhanced btn-enhanced mr-2">
                                    <div class="loading-spinner" id="loadingSpinner"></div>
                                    <i class="mdi mdi-shield-check" id="submitIcon"></i>
                                    Update Password
                                </button>
                                <a href="{{ route('lecturer.courses') }}" class="btn btn-secondary-enhanced btn-enhanced">
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
        strengthText.className = 'form-text text-muted mt-2';
    } else if (strength <= 2) {
        strengthBar.classList.add('strength-weak');
        text = '🔴 Weak - Add more variety';
        strengthText.className = 'form-text text-danger mt-2';
    } else if (strength <= 4) {
        strengthBar.classList.add('strength-medium');
        text = '🟡 Medium - Getting better';
        strengthText.className = 'form-text text-warning mt-2';
    } else if (strength <= 5) {
        strengthBar.classList.add('strength-good');
        text = '🟢 Good - Almost there';
        strengthText.className = 'form-text text-info mt-2';
    } else {
        strengthBar.classList.add('strength-strong');
        text = '✅ Strong - Excellent password!';
        strengthText.className = 'form-text text-success mt-2';
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
        matchText.innerHTML = '<i class="mdi mdi-check-circle text-success mr-1"></i><span class="text-success">Passwords match!</span>';
    } else {
        matchText.innerHTML = '<i class="mdi mdi-close-circle text-danger mr-1"></i><span class="text-danger">Passwords do not match</span>';
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
    submitBtn.innerHTML = submitBtn.innerHTML.replace('Update Password', 'Updating...');
});

// Auto-hide alerts after 5 seconds
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert-enhanced');
    alerts.forEach(function(alert) {
        alert.style.transition = 'all 0.5s ease';
        alert.style.opacity = '0';
        alert.style.transform = 'translateY(-10px)';
        setTimeout(function() {
            alert.remove();
        }, 500);
    });
}, 5000);
</script>
@endsection
