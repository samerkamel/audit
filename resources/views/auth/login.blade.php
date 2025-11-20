@php
$customizerHidden = 'customizer-hide';
@endphp

@extends('layouts/blankLayout')

@section('title', 'Login - Audit & Compliance Management')

@section('page-style')
@vite(['resources/assets/vendor/scss/pages/page-auth.scss'])
@endsection

@section('content')
<div class="container-xxl">
  <div class="authentication-wrapper authentication-basic container-p-y">
    <div class="authentication-inner py-6">
      <!-- Login Card -->
      <div class="card">
        <div class="card-body">
          <!-- Logo -->
          <div class="app-brand justify-content-center mb-6">
            <a href="{{ url('/') }}" class="app-brand-link">
              <span class="app-brand-logo demo">
                @include('_partials.macros', ['width' => '32', 'height' => '32'])
              </span>
              <span class="app-brand-text demo text-heading fw-bold ms-2">{{ config('variables.templateName') }}</span>
            </a>
          </div>
          <!-- /Logo -->

          <h4 class="mb-1">Welcome to Audit System! 👋</h4>
          <p class="mb-6">Please sign in to your account to continue</p>

          @if ($errors->any())
            <div class="alert alert-danger alert-dismissible" role="alert">
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              <div class="alert-message">
                @foreach ($errors->all() as $error)
                  <div>{{ $error }}</div>
                @endforeach
              </div>
            </div>
          @endif

          <form id="formAuthentication" class="mb-4" action="{{ route('login') }}" method="POST">
            @csrf

            <!-- Email -->
            <div class="mb-6">
              <label for="email" class="form-label">Email</label>
              <input
                type="email"
                class="form-control @error('email') is-invalid @enderror"
                id="email"
                name="email"
                value="{{ old('email') }}"
                placeholder="admin@alfa-electronics.com"
                autofocus
                required
              />
              @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <!-- Password -->
            <div class="mb-6 form-password-toggle">
              <label class="form-label" for="password">Password</label>
              <div class="input-group input-group-merge">
                <input
                  type="password"
                  id="password"
                  class="form-control @error('password') is-invalid @enderror"
                  name="password"
                  placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                  aria-describedby="password"
                  required
                />
                <span class="input-group-text cursor-pointer">
                  <i class="icon-base ti tabler-eye-off"></i>
                </span>
              </div>
              @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>

            <!-- Remember Me -->
            <div class="my-8">
              <div class="form-check mb-0">
                <input class="form-check-input" type="checkbox" id="remember" name="remember" />
                <label class="form-check-label" for="remember">
                  Remember Me
                </label>
              </div>
            </div>

            <!-- Submit Button -->
            <div class="mb-6">
              <button class="btn btn-primary d-grid w-100" type="submit">
                <span class="d-flex align-items-center justify-content-center">
                  <span>Sign In</span>
                  <i class="icon-base ti tabler-arrow-right ms-2"></i>
                </span>
              </button>
            </div>
          </form>

          <!-- Demo Credentials Info -->
          <div class="alert alert-info mb-0" role="alert">
            <h6 class="alert-heading mb-2">Demo Credentials</h6>
            <p class="mb-1"><strong>Email:</strong> admin@alfa-electronics.com</p>
            <p class="mb-0"><strong>Password:</strong> password</p>
          </div>

        </div>
      </div>
      <!-- /Login Card -->
    </div>
  </div>
</div>
@endsection
