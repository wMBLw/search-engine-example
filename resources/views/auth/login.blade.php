@extends('layouts.app')

@section('title', 'Login - Search Engine')

@section('styles')
<style>
    .login-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8fafc;
    }

    .login-card {
        width: 100%;
        max-width: 400px;
    }

    .btn-login {
        background: #2563eb;
        border: none;
        color: white;
    }

    .btn-login:hover {
        background: #1d4ed8;
    }
</style>
@endsection

@section('content')
<div class="login-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card login-card">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <h3>Search Engine</h3>
                            <p class="text-muted">Dashboard Login</p>
                        </div>

                        <form id="loginForm">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input
                                    type="email"
                                    class="form-control"
                                    id="email"
                                    name="email"
                                    placeholder="test user : test@example.com"
                                    required
                                >
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input
                                    type="password"
                                    class="form-control"
                                    id="password"
                                    name="password"
                                    placeholder="test password : password"
                                    required
                                >
                                <div class="invalid-feedback"></div>
                            </div>

                            <button type="submit" class="btn btn-login w-100" id="loginButton">
                                <span class="button-text">Login</span>
                                <span class="spinner-border spinner-border-sm ms-2 d-none"></span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    class LoginForm {
        constructor() {
            this.form = document.getElementById('loginForm');
            this.emailInput = document.getElementById('email');
            this.passwordInput = document.getElementById('password');
            this.submitButton = document.getElementById('loginButton');
            this.buttonText = this.submitButton.querySelector('.button-text');
            this.spinner = this.submitButton.querySelector('.spinner-border');

            this.init();
        }

        init() {
            this.form.addEventListener('submit', (e) => this.handleSubmit(e));
            this.checkExistingAuth();
        }

        checkExistingAuth() {
            const token = localStorage.getItem('auth_token');
            if (token) {
                window.location.href = '{{ route("dashboard") }}';
            }
        }

        async handleSubmit(e) {
            e.preventDefault();

            this.clearErrors();

            if (!this.validate()) {
                return;
            }

            this.setLoading(true);

            try {
                const formData = {
                    email: this.emailInput.value,
                    password: this.passwordInput.value
                };

                const response = await API.post('/login', formData);
                console.log('Login response:', response);

                const token = response.data?.access_token ||
                             response.data?.token ||
                             response.access_token ||
                             response.token;
                const user = response.data?.user || response.user;

                if (!token) {
                    throw new Error('Token not received from server');
                }

                // Store token
                localStorage.setItem('auth_token', token);

                // Store user info (optional)
                if (user) {
                    localStorage.setItem('user', JSON.stringify(user));
                }

                // Show success message
                Toast.success('Login successful! Redirecting...');

                // Redirect after short delay
                setTimeout(() => {
                    console.log('Redirecting to dashboard...');
                    window.location.href = '{{ route("dashboard") }}';
                }, 1000);

            } catch (error) {
                console.error('Login error:', error);
                this.handleError(error);
            } finally {
                this.setLoading(false);
            }
        }

        validate() {
            let isValid = true;

            // Email validation
            if (!this.emailInput.value || !this.isValidEmail(this.emailInput.value)) {
                this.showError(this.emailInput, 'Please enter a valid email address');
                isValid = false;
            }

            // Password validation
            if (!this.passwordInput.value || this.passwordInput.value.length < 4) {
                this.showError(this.passwordInput, 'Password must be at least 4 characters');
                isValid = false;
            }

            return isValid;
        }

        isValidEmail(email) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        }

        showError(input, message) {
            input.classList.add('is-invalid');
            const feedback = input.parentElement.querySelector('.invalid-feedback') ||
                           input.nextElementSibling;
            if (feedback) {
                feedback.textContent = message;
            }
        }

        clearErrors() {
            this.form.querySelectorAll('.is-invalid').forEach(el => {
                el.classList.remove('is-invalid');
            });
        }

        handleError(error) {
            if (error.errors) {
                Object.keys(error.errors).forEach(field => {
                    const input = this.form.querySelector(`[name="${field}"]`);
                    if (input) {
                        this.showError(input, error.errors[field][0]);
                    }
                });
            } else {
                Toast.error(error.message || 'Login failed. Please check your credentials.');
            }
        }

        setLoading(loading) {
            this.submitButton.disabled = loading;
            this.emailInput.disabled = loading;
            this.passwordInput.disabled = loading;

            if (loading) {
                this.buttonText.classList.add('d-none');
                this.spinner.classList.remove('d-none');
            } else {
                this.buttonText.classList.remove('d-none');
                this.spinner.classList.add('d-none');
            }
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => new LoginForm());
    } else {
        new LoginForm();
    }
</script>
@endsection

