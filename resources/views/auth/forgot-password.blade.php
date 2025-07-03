@extends('layouts.app')
@section('app-content')

<div class="container mt-5">
    <div class="card">
        <div class="card-header bg-dark text-white text-center fs-4">
            <span id="form-title">Forgot Password</span>
        </div>

        <form action="{{ route('password.email') }}" method="POST">
            @csrf
            <div class="card-body">
                <!-- Description -->
                <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
                </div>

                <!-- Session Status -->
                @if (session('status'))
                    <div class="alert alert-success mb-4">
                        {{ session('status') }}
                    </div>
                @endif

                <!-- Email Input -->
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        class="form-control"
                        value="{{ old('email') }}"
                        required
                        autofocus
                    >
                    @error('email')
                        <div class="text-danger mt-2">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

            </div>
            <div class="card-footer bg-dark d-flex justify-content-between">
                <a class="text-decoration-none text-white" href="{{route('login')}}">Login</a>
                <button type="submit" class="btn btn-primary">
                    {{ __('Email Password Reset Link') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
@push('custom-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.querySelector('form[action="{{ route('password.email') }}"]');
            const submitButton = form.querySelector('button[type="submit"]');

            form.addEventListener('submit', function () {
                submitButton.disabled = true;
                submitButton.textContent = 'Sending...';
            });
        });
    </script>
@endpush
