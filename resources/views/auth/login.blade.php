@extends('layouts.app')
@section('app-content')

<div class="container mt-5">
    <div class="card">
        <div class="card-header text-white text-center bg-dark fs-4">
            <span id="form-title">Login</span>
        </div>

        <!-- Login Form -->
        <form action="{{route('login')}}" method="POST" id="login-form">
            @csrf
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <input type="email" class="form-control" name="email" id="email" placeholder="Email" value="{{ old('email') }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <input type="password" class="form-control" name="password" id="password" placeholder="Password" required>
                            <!-- Toggle show password -->
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="show-password" />
                                <label class="form-check-label" for="show-password">Show Password</label>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Link to Signup form -->
                <div class="form-group text-center d-flex justify-content-between mb-3">
                    <p>Don't have an account? <a href="javascript:void(0);" id="show-signup-form">Sign up</a></p>
                    <a href="{{ route('password.request') }}">fogot passoword?</a>
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
                            <input type="password" class="form-control" name="password_confirmation" id="signup-password-confirmation" placeholder="Confirmation Password" required>
                        </div>
                    </div>
                </div>

                <!-- Link to Login form -->
                <div class="form-group text-center mb-3">
                    <p>Already have an account? <a href="javascript:void(0);" id="show-login-form">Login</a></p>
                </div>
            </div>
            <div class="card-footer bg-dark d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">Signup</button>
            </div>
        </form>
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

            document.getElementById('signup-form').addEventListener('submit', function (e) {
                e.preventDefault();

                const form = this;
                const formData = new FormData(form);
                const submitButton = form.querySelector('button[type="submit"]');

                submitButton.disabled = true;
                submitButton.textContent = 'Submitting...';

                axios.post("{{ route('register') }}", formData)
                    .then(response => {
                        const res = response.data;

                        if (res.status == 200) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: res.message,
                                showConfirmButton: false,
                                timer: 1500,
                                timerProgressBar: true
                            });

                            setTimeout(() => {
                                window.location.href = res.redirect_url;
                            }, 2000);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: res.message,
                                showConfirmButton: false,
                                timer: 1500,
                                timerProgressBar: true
                            });
                        }
                    })
                    .catch(error => {
                        if (error.response && error.response.status === 422 && error.response.data.errors) {
                            let errorMessage = 'Validation errors occurred:';
                            const errors = error.response.data.errors;

                            for (const field in errors) {
                                errorMessage += `\n${errors[field].join(', ')}`;
                            }

                            Swal.fire({
                                icon: 'error',
                                title: 'Validation Errors!',
                                text: errorMessage,
                                confirmButtonText: 'OK'
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Something went wrong. Please try again.',
                                confirmButtonText: 'OK'
                            });
                        }
                    })
                    .finally(() => {
                        
                        submitButton.disabled = false;
                        submitButton.textContent = 'Sign Up';
                    });
            });

        });
    </script>

@endpush
