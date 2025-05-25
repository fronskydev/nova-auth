<div class="auth-content">
    <div class="card border-0 shadow-sm">
        <div class="card-body bg-warning-subtle default-rounded text-center p-4">
            <div class="d-flex flex-column align-items-center">
                <i class="bi bi-exclamation-triangle-fill text-warning fs-1"></i>
                <h5 class="mt-2 text-warning">Verification Link Expired</h5>
                <p>Your email verification link has expired.</p>
                <ul class="text-start">
                    <li>A new verification link has been sent to your email.</li>
                    <li>Check your inbox and spam folder.</li>
                    <li>If you don't see the email, please <strong><a href="mailto:<?= $_ENV["MAIL_REPLY_TO_ADDRESS"] ?>">contact support</a></strong>.
                    </li>
                </ul>

                <div class="mt-4">
                    <a href="<?= PUBLIC_URL . "/login" ?>" class="btn btn-warning default-rounded">Go to Login</a>
                </div>
            </div>
        </div>
    </div>
</div>
