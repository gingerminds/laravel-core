@extends('gingerminds-core::layouts.master-without-nav')

@section('title')
    @lang('gingerminds-core::translation.title.login')
@endsection

@section('body')

    <body>
    @endsection

    @section('content')
        <div class="account-pages my-5 pt-sm-5">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-6 col-xl-5">
                        <div class="card overflow-hidden">
                            <div class="bg-primary-subtle">
                                <div class="row">
                                    <div class="col-7">
                                        <div class="text-primary p-4">
                                            <h5 class="text-primary">@lang('gingerminds-core::translation.message.login.welcome_back')</h5>
                                            <p>@lang('gingerminds-core::translation.message.login.sign_in_to_access').</p>
                                        </div>
                                    </div>
                                    <div class="col-5 align-self-end">
                                        <img src="{{ URL::asset('build/images/profile-img.png') }}" alt="" class="img-fluid">
                                    </div>
                                </div>
                            </div>
                            <div class="card-body pt-0">
                                <div class="auth-logo">
                                    <a href="{{ route('dashboard') }}" class="auth-logo-light">
                                        <div class="avatar-md profile-user-wid mb-4">
                                            <span class="avatar-title rounded-circle bg-light">
                                                <img src="{{ URL::asset('build/images/logo.svg') }}" alt="" class="rounded-circle" height="34">
                                            </span>
                                        </div>
                                    </a>

                                    <a href="{{ route('dashboard') }}" class="auth-logo-dark">
                                        <div class="avatar-md profile-user-wid mb-4">
                                            <span class="avatar-title rounded-circle bg-light">
                                                <img src="{{ URL::asset('build/images/logo.svg') }}" alt="" class="rounded-circle" height="34">
                                            </span>
                                        </div>
                                    </a>
                                </div>
                                <div class="p-2">
                                    <form class="form-horizontal" action="{{ route('gingerminds-core.authenticate') }}" method="POST">
                                        @csrf
                                        @error('message')
                                            <div class="col-xl-12">
                                                <div class="alert alert-danger">{{ $message }}</div>
                                            </div>
                                        @enderror

                                        <div class="mb-3">
                                            <label for="username" class="form-label">@lang('gingerminds-core::translation.auth.form.login')</label>
                                            <input type="email" name="username" class="form-control" id="username" placeholder="@lang('gingerminds-core::translation.auth.form.login_placeholder')" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="password" class="form-label">@lang('gingerminds-core::translation.auth.form.password')</label>
                                            <div class="input-group auth-pass-inputgroup">
                                                <input type="password" name="password" class="form-control" id="password" placeholder="@lang('gingerminds-core::translation.auth.form.password_placeholder')" aria-label="Password" required>
                                                <button class="btn btn-light " type="button" id="password-addon">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="remember-check" name="remember">
                                            <label class="form-check-label" for="remember-check">
                                                @lang('gingerminds-core::translation.auth.form.remember')
                                            </label>
                                        </div>

                                        <div class="mt-3 d-grid">
                                            <button class="btn btn-primary waves-effect waves-light" type="submit">@lang('gingerminds-core::translation.auth.form.login')</button>
                                        </div>
                                    </form>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- end account-pages -->

@endsection
