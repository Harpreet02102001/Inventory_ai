<x-guest-layout>
    {{--
        Login Page — Bootstrap 5
        Matches the design: centered white card on gray background,
        logo at top, email + password fields, remember me checkbox,
        forgot password link, login button.
    --}}
    <div style="width: 100%; max-width: 420px; padding: 1rem;">

        {{-- Logo + Title --}}
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center rounded-3 mb-3"
                style="width:56px; height:56px; background-color:#2563EB;">
                <i class="bi bi-box-seam text-white" style="font-size:1.5rem;"></i>
            </div>
            <h1 class="fw-bold text-dark mb-1" style="font-size:1.6rem;">
                Mini Inventory System
            </h1>
            <p class="text-muted small">Manage products, suppliers, and stock easily</p>
        </div>

        {{-- Login Card --}}
        <div class="card shadow-sm" style="border-radius:1rem; border:1px solid #e5e7eb;">
            <div class="card-body p-4">

                {{-- Session status (e.g. password reset success message) --}}
                @if(session('status'))
                <div class="alert alert-success alert-dismissible small mb-3" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    {{-- Email Field --}}
                    <div class="mb-3">
                        <label for="email" class="form-label fw-medium">
                            Email
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-envelope text-muted"></i>
                            </span>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="{{ old('email') }}"
                                class="form-control border-start-0 ps-0 @error('email') is-invalid @enderror"
                                placeholder="Enter your email"
                                required
                                autofocus
                                autocomplete="username">
                            @error('email')
                            <div class="invalid-feedback">
                                <i class="bi bi-exclamation-circle"></i>
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                    </div>

                    {{-- Password Field --}}
                    <div class="mb-3">
                        <label for="password" class="form-label fw-medium">
                            Password
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-lock text-muted"></i>
                            </span>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-control border-start-0 ps-0 @error('password') is-invalid @enderror"
                                placeholder="Enter your password"
                                required
                                autocomplete="current-password">
                            {{-- Toggle password visibility --}}
                            <button
                                type="button"
                                class="input-group-text bg-white border-start-0"
                                onclick="togglePasswordVisibility()"
                                title="Show/hide password"
                                style="cursor:pointer;">
                                <i class="bi bi-eye-slash text-muted" id="password-toggle-icon"></i>
                            </button>
                            @error('password')
                            <div class="invalid-feedback">
                                <i class="bi bi-exclamation-circle"></i>
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                    </div>

                    {{-- Remember Me --}}
                    <div class="mb-4">
                        <div class="form-check">
                            <input
                                type="checkbox"
                                id="remember_me"
                                name="remember"
                                class="form-check-input">
                            <label for="remember_me" class="form-check-label text-muted small">
                                Remember me
                            </label>
                        </div>
                    </div>

                    {{-- Login Button --}}
                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary py-2 text-center">
                            <i class="bi bi-lock me-2"></i>
                            <span>Login</span>
                        </button>
                    </div>

                    {{-- Divider --}}
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <hr class="flex-grow-1 m-0">
                        <span class="text-muted small">or</span>
                        <hr class="flex-grow-1 m-0">
                    </div>

                    {{-- Forgot Password --}}
                    @if(Route::has('password.request'))
                    <div class="text-center">
                        <a
                            href="{{ route('password.request') }}"
                            class="text-primary small text-decoration-none">
                            <i class="bi bi-lock me-1"></i>
                            Forgot your password?
                        </a>
                    </div>
                    @endif

                </form>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        /**
         * Toggle password field between text and password type.
         * Updates the eye icon to reflect the current visibility state.
         */
        function togglePasswordVisibility() {
            const input = document.getElementById('password');
            const icon = document.getElementById('password-toggle-icon');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            } else {
                input.type = 'password';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
            }
        }
    </script>
    @endpush
</x-guest-layout>