@auth
    @unless(auth()->user()->hasVerifiedEmail())
        <div class="email-verification-banner" id="emailVerificationBanner" style="background-color: #fff3cd; border-bottom: 1px solid #ffc107; color: #664d03; padding: 0.75rem 1rem; font-size: 0.9rem; position: relative; z-index: 1020;">
            <div class="container d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-envelope-exclamation"></i>
                    <span>
                        <strong>Verify your email to unlock all features.</strong>
                        Please check your inbox
                        @if(auth()->user()->email)
                            (<strong>{{ auth()->user()->email }}</strong>)
                        @endif
                        and click the verification link to secure your account.
                    </span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <form method="POST" action="{{ route('verification.resend') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-warning text-dark fw-semibold">
                            <i class="bi bi-send me-1"></i>Resend Email
                        </button>
                    </form>
                    <button type="button" class="btn-close btn-close-sm" aria-label="Dismiss" onclick="document.getElementById('emailVerificationBanner').style.display='none';"></button>
                </div>
            </div>
        </div>
    @endunless
@endauth

@if(session('resent'))
    <div class="alert alert-success alert-dismissible fade show rounded-0 mb-0" role="alert">
        <div class="container">
            <i class="bi bi-check-circle me-2"></i>A fresh verification link has been sent to your email address.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
@endif
