@extends('layouts.app')
@section('app-content')

    <div class="container mt-5">
        <div class="card">
            <div class="card-header bg-dark text-white text-center fs-4">
                <span id="form-title">Reset Password</span>
            </div>

            <form action="{{ route('password.store') }}" method="POST">
                @csrf

                <div class="card-body">
                    <!-- Hidden Token -->
                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                    <!-- Email -->
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input
                            type="email"
                            name="email"
                            id="email"
                            class="form-control"
                            value="{{ old('email', $request->email) }}"
                            required
                            autofocus
                            autocomplete="username"
                        >
                        @error('email')
                            <div class="text-danger mt-2">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="mb-3">
                        <label for="password" class="form-label">New Password</label>
                        <input
                            type="password"
                            name="password"
                            id="password"
                            class="form-control"
                            required
                            autocomplete="new-password"
                        >
                        @error('password')
                            <div class="text-danger mt-2">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirm New Password</label>
                        <input
                            type="password"
                            name="password_confirmation"
                            id="password_confirmation"
                            class="form-control"
                            required
                            autocomplete="new-password"
                        >
                        @error('password_confirmation')
                            <div class="text-danger mt-2">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                <div class="card-footer bg-dark d-flex justify-content-between">
                    <a href="{{ route('login') }}" class="text-white">Login</a>
                    <button type="submit" class="btn btn-primary">
                        {{ __('Reset Password') }}
                    </button>
                </div>
            </form>
        </div>

    </div>
@endsection
