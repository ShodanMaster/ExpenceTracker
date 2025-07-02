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
                            <input type="text" class="form-control" name="email" id="email" placeholder="Email" value="{{ old('email') }}" required>
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
                <div class="form-group text-center mb-3">
                    <p>Don't have an account? <a href="javascript:void(0);" id="show-signup-form">Sign up</a></p>
                </div>
            </div>
            <div class="card-footer bg-dark d-flex justify-content-between">

                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="rememberMe" name="rememberMe" />
                    <label class="form-check-label text-white" for="rememberMe">Remember me</label>
                </div>

                <button class="btn btn-primary">Login</button>
            </div>
        </form>


        <!-- Signup Form -->
        <form id="signup-form" style="display: none;">
            <div class="card-body">
                <div class="form-group mb-3">
                    <input type="text" class="form-control" name="username" id="name" placeholder="User Name" required>
                </div>
                <div class="form-group mb-3">
                    <input type="email" class="form-control" name="email" id="signup-email" placeholder="Email" required>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <input type="password" class="form-control" name="password" id="signup-password" placeholder="Password" required>
                            <!-- Toggle show password -->
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
                <button class="btn btn-primary">Signup</button>
            </div>
        </form>
    </div>
</div>

@endsection
@push('custom-scripts')
    <script>
        $(document).ready(function () {
            $('#show-signup-form').click(function() {
                $('#login-form').hide();
                $('#signup-form').show();
                $('#form-title').text('Signup');
            });

            $('#show-login-form').click(function() {
                $('#signup-form').hide();
                $('#login-form').show();
                $('#form-title').text('Login');
            });

            $('#show-password').change(function() {
                var passwordField = $('#password');
                if ($(this).prop('checked')) {
                    passwordField.attr('type', 'text');
                } else {
                    passwordField.attr('type', 'password');
                }
            });

            $('#show-signup-password').change(function() {
                var signupPasswordField = $('#signup-password');
                if ($(this).prop('checked')) {
                    signupPasswordField.attr('type', 'text');
                } else {
                    signupPasswordField.attr('type', 'password');
                }
            });
        });
    </script>
@endpush
