<!doctype html>
<html lang="en">
<head>
    <title>Verify Code - House of Cars</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
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
        }

        .mfa-container {
            background: #ffffff;
            border-radius: 20px;
            padding: 3rem 2.5rem;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
            border: 1px solid #f1f1f1;
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

        .mfa-header {
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
        }

        .logo-icon i {
            color: white;
            font-size: 1.8rem;
        }

        .mfa-title {
            color: #1f2937;
            font-size: 1.75rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .mfa-subtitle {
            color: #6b7280;
            font-size: 0.95rem;
            font-weight: 400;
            line-height: 1.5;
        }

        .code-inputs {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
            margin: 2rem 0;
        }

        .code-input {
            width: 50px;
            height: 60px;
            text-align: center;
            font-size: 1.5rem;
            font-weight: 600;
            border: 2px solid #f3f4f6;
            border-radius: 12px;
            background: #fafafa;
            transition: all 0.3s ease;
            outline: none;
        }

        .code-input:focus {
            border-color: #ef4444;
            background: white;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
            transform: translateY(-2px);
        }

        .code-input.is-invalid {
            border-color: #ef4444;
            animation: shake 0.5s;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        .verify-btn {
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
            margin-bottom: 1rem;
        }

        .verify-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(239, 68, 68, 0.4);
        }

        .verify-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .resend-section {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #f3f4f6;
        }

        .resend-text {
            color: #6b7280;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .resend-btn {
            background: none;
            border: none;
            color: #ef4444;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            padding: 0.5rem 1rem;
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .resend-btn:hover:not(:disabled) {
            background: rgba(239, 68, 68, 0.1);
        }

        .resend-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .timer {
            display: inline-block;
            color: #ef4444;
            font-weight: 600;
        }

        .alert {
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        .alert-error {
            background: #fef2f2;
            color: #ef4444;
            border: 1px solid #fecaca;
        }

        .alert-success {
            background: #f0fdf4;
            color: #16a34a;
            border: 1px solid #bbf7d0;
        }

        .back-link {
            text-align: center;
            margin-top: 1rem;
        }

        .back-link a {
            color: #6b7280;
            text-decoration: none;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .back-link a:hover {
            background: #f9fafb;
            color: #1f2937;
        }

        @media (max-width: 768px) {
            .mfa-container {
                margin: 1rem;
                padding: 2rem 1.5rem;
            }
            
            .code-input {
                width: 45px;
                height: 55px;
                font-size: 1.3rem;
            }
        }
    </style>
</head>
<body>
    <div class="mfa-container">
        <div class="mfa-header">
            <div class="logo-icon">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h1 class="mfa-title">Verify Your Identity</h1>
            <p class="mfa-subtitle">Enter the 6-digit code sent to your phone</p>
        </div>

        <!-- Error Messages -->
        <div id="errorAlert" class="alert alert-error" style="display: none;">
            <i class="fas fa-exclamation-circle"></i>
            <span id="errorMessage"></span>
        </div>

        <!-- Success Messages -->
        <div id="successAlert" class="alert alert-success" style="display: none;">
            <i class="fas fa-check-circle"></i>
            <span id="successMessage"></span>
        </div>

        <form method="POST" action="{{ route('mfa.verify') }}" id="mfaForm">
            @csrf
            <div class="code-inputs">
                <input type="text" class="code-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off">
                <input type="text" class="code-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off">
                <input type="text" class="code-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off">
                <input type="text" class="code-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off">
                <input type="text" class="code-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off">
                <input type="text" class="code-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off">
            </div>
            <input type="hidden" name="code" id="fullCode">
            
            <button type="submit" class="verify-btn" id="verifyBtn">
                Verify Code
            </button>
        </form>

        <div class="resend-section">
            <p class="resend-text">Didn't receive the code?</p>
            <form method="POST" action="{{ route('mfa.resend') }}" id="resendForm" style="display: inline;">
                @csrf
                <button type="submit" class="resend-btn" id="resendBtn">
                    Resend Code <span class="timer" id="timer"></span>
                </button>
            </form>
        </div>

        <div class="back-link">
            <a href="{{ route('login') }}">
                <i class="fas fa-arrow-left"></i>
                Back to Login
            </a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('.code-input');
            const form = document.getElementById('mfaForm');
            const resendForm = document.getElementById('resendForm');
            const resendBtn = document.getElementById('resendBtn');
            const timerSpan = document.getElementById('timer');
            const fullCodeInput = document.getElementById('fullCode');
            
            let resendTimer = 60;
            let timerInterval;

            // Auto-focus first input
            inputs[0].focus();

            // Handle input navigation
            inputs.forEach((input, index) => {
                input.addEventListener('input', function(e) {
                    const value = e.target.value;
                    
                    // Only allow numbers
                    if (!/^\d*$/.test(value)) {
                        e.target.value = '';
                        return;
                    }

                    // Move to next input
                    if (value && index < inputs.length - 1) {
                        inputs[index + 1].focus();
                    }

                    // Auto-submit when all filled
                    checkAndSubmit();
                });

                input.addEventListener('keydown', function(e) {
                    // Handle backspace
                    if (e.key === 'Backspace' && !input.value && index > 0) {
                        inputs[index - 1].focus();
                    }

                    // Handle paste
                    if (e.key === 'v' && (e.ctrlKey || e.metaKey)) {
                        e.preventDefault();
                        navigator.clipboard.readText().then(text => {
                            const code = text.replace(/\D/g, '').slice(0, 6);
                            code.split('').forEach((digit, i) => {
                                if (inputs[i]) inputs[i].value = digit;
                            });
                            checkAndSubmit();
                        });
                    }
                });
            });

            function checkAndSubmit() {
                const code = Array.from(inputs).map(input => input.value).join('');
                if (code.length === 6) {
                    fullCodeInput.value = code;
                    form.submit();
                }
            }

            // Resend timer
            function startResendTimer() {
                resendBtn.disabled = true;
                timerInterval = setInterval(() => {
                    resendTimer--;
                    timerSpan.textContent = `(${resendTimer}s)`;
                    
                    if (resendTimer <= 0) {
                        clearInterval(timerInterval);
                        resendBtn.disabled = false;
                        timerSpan.textContent = '';
                        resendTimer = 60;
                    }
                }, 1000);
            }

            startResendTimer();

            // Handle resend
            resendForm.addEventListener('submit', function() {
                resendTimer = 60;
                startResendTimer();
            });

            // Show errors if any
            const urlParams = new URLSearchParams(window.location.search);
            const error = urlParams.get('error');
            if (error) {
                document.getElementById('errorAlert').style.display = 'block';
                document.getElementById('errorMessage').textContent = error;
                inputs.forEach(input => input.classList.add('is-invalid'));
            }
        });
    </script>
</body>
</html>