@extends('layouts.app')
@section('app-content')

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card">
                <div class="card-header text-white text-center bg-dark fs-4">
                    <span id="form-title">Login</span>
                </div>

                <!-- Login Form -->
                <form action="{{route('login')}}" method="POST" id="login-form">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <input type="email" class="form-control mb-3" name="email" id="email" placeholder="Email" value="{{ old('email') }}" required>
                        </div>
                        <div class="form-group">
                            <input type="password" class="form-control mb-3" name="password" id="password" placeholder="Password" required>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <div class="form-check mb-0">
                                    <input class="form-check-input" type="checkbox" id="show-password" />
                                    <label class="form-check-label" for="show-password">Show Password</label>
                                </div>
                                <a href="{{ route('password.request') }}" class="small text-decoration-none">Forgot password?</a>
                            </div>
                        </div>

                        <!-- Signup link centered below inputs -->
                        <div class="text-center my-4">
                            <p class="mb-0">Don't have an account? <a href="javascript:void(0);" id="show-signup-form" class="text-primary text-decoration-none">Sign up</a></p>
                        </div>

                        <!-- Google login button -->
                        <div class="d-flex justify-content-center mb-3">
                            <a href="{{ url('auth/google') }}" class="btn btn-primary d-flex align-items-center justify-content-center" style="width: 180px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-google me-2" viewBox="0 0 16 16">
                                    <path d="M15.545 6.558a9.4 9.4 0 0 1 .139 1.626c0 2.434-.87 4.492-2.384 5.885h.002C11.978 15.292 10.158 16 8 16A8 8 0 1 1 8 0a7.7 7.7 0 0 1 5.352 2.082l-2.284 2.284A4.35 4.35 0 0 0 8 3.166c-2.087 0-3.86 1.408-4.492 3.304a4.8 4.8 0 0 0 0 3.063h.003c.635 1.893 2.405 3.301 4.492 3.301 1.078 0 2.004-.276 2.722-.764h-.003a3.7 3.7 0 0 0 1.599-2.431H8v-3.08z"/>
                                </svg> Login with Google
                            </a>
                        </div>
                    </div>

                    <div class="card-footer bg-dark d-flex justify-content-between">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="rememberMe" name="remember" />
                            <label class="form-check-label text-white" for="rememberMe">Remember me</label>
                        </div>
                        <button class="btn btn-primary">Login</button>
                    </div>
                </form>

                <!-- Signup Form -->
                <form id="signup-form" style="display: none;">
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <input type="text" class="form-control" name="name" id="name" placeholder="Full Name" required>
                        </div>
                        <div class="form-group mb-3">
                            <input type="email" class="form-control" name="email" id="signup-email" placeholder="Email" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <input type="password" class="form-control" name="password" id="signup-password" placeholder="Password" required>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="show-signup-password" />
                                        <label class="form-check-label" for="show-signup-password">Show Password</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <input type="password" class="form-control" name="password_confirmation" id="signup-password-confirmation" placeholder="Confirm Password" required>
                                </div>
                            </div>
                        </div>

                        <!-- Link to Login form -->
                        <div class="form-group text-center mb-3">
                            <p>Already have an account? <a href="javascript:void(0);" id="show-login-form">Login</a></p>
                        </div>

                        <!-- Google login button -->
                        <div class="d-flex justify-content-center mb-3">
                            <a href="{{ url('auth/google') }}" class="btn btn-primary d-flex align-items-center justify-content-center" style="width: 180px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-google me-2" viewBox="0 0 16 16">
                                    <path d="M15.545 6.558a9.4 9.4 0 0 1 .139 1.626c0 2.434-.87 4.492-2.384 5.885h.002C11.978 15.292 10.158 16 8 16A8 8 0 1 1 8 0a7.7 7.7 0 0 1 5.352 2.082l-2.284 2.284A4.35 4.35 0 0 0 8 3.166c-2.087 0-3.86 1.408-4.492 3.304a4.8 4.8 0 0 0 0 3.063h.003c.635 1.893 2.405 3.301 4.492 3.301 1.078 0 2.004-.276 2.722-.764h-.003a3.7 3.7 0 0 0 1.599-2.431H8v-3.08z"/>
                                </svg> Login with Google
                            </a>
                        </div>
                    </div>
                    <div class="card-footer bg-dark d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">Signup</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('custom-scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('show-signup-form').addEventListener('click', function () {
            document.getElementById('login-form').style.display = 'none';
            document.getElementById('signup-form').style.display = 'block';
            document.getElementById('form-title').textContent = 'Signup';
        });

        document.getElementById('show-login-form').addEventListener('click', function () {
            document.getElementById('signup-form').style.display = 'none';
            document.getElementById('login-form').style.display = 'block';
            document.getElementById('form-title').textContent = 'Login';
        });

        document.getElementById('show-password').addEventListener('change', function () {
            const passwordField = document.getElementById('password');
            passwordField.type = this.checked ? 'text' : 'password';
        });

        document.getElementById('show-signup-password').addEventListener('change', function () {
            const passwordField = document.getElementById('signup-password');
            passwordField.type = this.checked ? 'text' : 'password';
        });

        document.getElementById('signup-form').addEventListener('submit', async function (e) {
            e.preventDefault();

            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('signup-email').value.trim();
            const password = document.getElementById('signup-password').value;
            const confirmPassword = document.getElementById('signup-password-confirmation').value;

            if (password !== confirmPassword) {
                Swal.fire({
                    icon: 'error',
                    title: 'Password Mismatch',
                    text: 'The password and confirmation do not match.',
                    confirmButtonColor: '#3085d6',
                });
                return;
            }

            try {
                const response = await axios.post('/register', {
                    name: name,
                    email: email,
                    password: password,
                    password_confirmation: confirmPassword,
                });

                if (response.data.status === 200) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Registration Successful',
                        text: response.data.message,
                        confirmButtonColor: '#3085d6',
                    }).then(() => {
                        // Redirect to dashboard
                        window.location.href = response.data.redirect_url;
                    });
                }

            } catch (error) {
                if (error.response && error.response.status === 422) {
                    const errors = error.response.data.errors;
                    let messages = Object.values(errors).flat().join('\n');

                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: messages,
                        confirmButtonColor: '#d33',
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Server Error',
                        text: 'Something went wrong. Please try again later.',
                        confirmButtonColor: '#d33',
                    });
                }
            }
        });

    });
</script>
@endpush
