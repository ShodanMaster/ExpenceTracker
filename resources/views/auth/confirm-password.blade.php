@extends('layouts.app')
@section('app-content')

    <div class="container mt-5">
        <div class="card">
            <div class="card-header bg-dark text-white text-center fs-4">
                <span id="form-title">Confirm Password</span>
            </div>

            <form action="{{ route('password.confirm') }}" method="POST">
                @csrf
                <div class="card-body">
                    <!-- Info Text -->
                    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                        {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
                    </div>

                    <!-- Password Input -->
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input
                            type="password"
                            name="password"
                            id="password"
                            class="form-control"
                            required
                            autocomplete="current-password"
                        >
                        @error('password')
                            <div class="text-danger mt-2">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                <div class="card-footer bg-dark d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">
                        {{ __('Confirm') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
