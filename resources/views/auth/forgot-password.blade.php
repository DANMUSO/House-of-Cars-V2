<!doctype html>
<html lang="en">
<head>
    <title>Password Reset - House of Cars</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="{{asset('dashboardv1/assets/images/houseofcars.png')}}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #ffffff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .reset-container {
            background: #ffffff;
            border-radius: 20px;
            padding: 3rem 2.5rem;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
            border: 1px solid #f1f1f1;
            position: relative;
            z-index: 1;
            animation: slideUp 0.8s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .reset-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .logo-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            box-shadow: 0 15px 35px rgba(239, 68, 68, 0.3);
            animation: logoFloat 3s ease-in-out infinite;
        }

        @keyframes logoFloat {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-5px); }
        }

        .logo-icon i {
            color: white;
            font-size: 1.8rem;
        }

        .reset-title {
            color: #1f2937;
            font-size: 1.75rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .reset-subtitle {
            color: #6b7280;
            font-size: 0.95rem;
            font-weight: 400;
        }

        .form-group {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .form-control {
            width: 100%;
            padding: 1rem 1rem 1rem 3rem;
            border: 2px solid #f3f4f6;
            border-radius: 16px;
            font-size: 1rem;
            font-family: inherit;
            background: #fafafa;
            transition: all 0.3s ease;
            outline: none;
        }

        .form-control:focus {
            border-color: #ef4444;
            background: white;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
            transform: translateY(-2px);
        }

        .form-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 1.1rem;
            transition: color 0.3s ease;
        }

        .form-control:focus + .form-icon {
            color: #ef4444;
        }

        .form-text {
            font-size: 0.85rem;
            color: #6b7280;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-text i {
            color: #9ca3af;
        }

        .reset-btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);
            color: white;
            border: none;
            border-radius: 16px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .reset-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .reset-btn:hover::before {
            left: 100%;
        }

        .reset-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(239, 68, 68, 0.4);
        }

        .reset-btn:active {
            transform: translateY(0);
        }

        .reset-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .back-to-login {
            text-align: center;
            margin-top: 1rem;
        }

        .back-to-login a {
            color: #ef4444;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 12px;
            transition: all 0.3s ease;
            position: relative;
        }

        .back-to-login a:hover {
            background: rgba(239, 68, 68, 0.1);
            transform: translateY(-1px);
            color: #dc2626;
        }

        .back-to-login a i {
            font-size: 0.8rem;
        }

        .alert {
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            animation: alertSlide 0.3s ease-out;
        }

        @keyframes alertSlide {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.1);
            color: #15803d;
            border: 1px solid rgba(34, 197, 94, 0.2);
        }

        .alert-success::before {
            content: '\f00c';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            color: #15803d;
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            color: #dc2626;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .alert-danger::before {
            content: '\f071';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            color: #dc2626;
        }

        /* Loading animation */
        .btn-loading {
            position: relative;
            color: transparent !important;
        }

        .btn-loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: translate(-50%, -50%) rotate(0deg); }
            100% { transform: translate(-50%, -50%) rotate(360deg); }
        }

        /* Mobile responsiveness */
        @media (max-width: 768px) {
            .reset-container {
                margin: 1rem;
                padding: 2rem 1.5rem;
            }
            
            .reset-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="reset-header">
            <div class="logo-icon">
                <i class="fas fa-mobile-alt"></i>
            </div>
            <h1 class="reset-title">Reset Password</h1>
            <p class="reset-subtitle">Enter your phone number to receive a new password</p>
        </div>

        <div id="message-container"></div>

        <form id="sms-reset-form" class="reset-form">
            <div class="form-group">
                <input id="phone" type="text" class="form-control" 
                       placeholder="Phone Number (254700000000)" name="phone" 
                       pattern="^254[17]\d{8}$" required>
                <i class="fas fa-phone form-icon"></i>
                <div class="form-text">
                    <i class="fas fa-info-circle"></i>
                    Enter your phone number in format: 254700000000
                </div>
            </div>

            <button type="submit" class="reset-btn" id="submit-btn">
                <span>Send New Password</span>
            </button>
        </form>

        <div class="back-to-login">
            <a href="{{ route('login') }}">
                <i class="fas fa-arrow-left"></i>
                Back to Login
            </a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('sms-reset-form');
            const submitBtn = document.getElementById('submit-btn');
            const messageContainer = document.getElementById('message-container');
            const phoneInput = document.getElementById('phone');

            // Enhanced form interactions
            phoneInput.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });

            phoneInput.addEventListener('blur', function() {
                if (this.value === '') {
                    this.parentElement.classList.remove('focused');
                }
            });

            // Form submission
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Show loading state
                submitBtn.classList.add('btn-loading');
                submitBtn.disabled = true;
                messageContainer.innerHTML = '';
                
                // Create FormData
                const formData = new FormData();
                formData.append('phone', phoneInput.value);
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                
                // Make AJAX request
                fetch('{{ route("password.sms.send") }}', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.message) {
                        messageContainer.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
                        form.reset();
                    } else if (data.error) {
                        messageContainer.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
                    }
                })
                .catch(error => {
                    messageContainer.innerHTML = '<div class="alert alert-danger">An error occurred. Please try again.</div>';
                })
                .finally(() => {
                    submitBtn.classList.remove('btn-loading');
                    submitBtn.disabled = false;
                });
            });

            // Phone number formatting and validation
            phoneInput.addEventListener('input', function() {
                let value = this.value.replace(/\D/g, ''); // Remove non-digits
                
                // Ensure it starts with 254
                if (value.length > 0 && !value.startsWith('254')) {
                    if (value.startsWith('7') || value.startsWith('1')) {
                        value = '254' + value;
                    }
                }
                
                // Limit to 12 digits (254 + 9 digits)
                if (value.length > 12) {
                    value = value.substring(0, 12);
                }
                
                this.value = value;
            });
        });
    </script>
</body>
</html>