<div class="auth-content">
    <div class="card border-0 shadow-sm">
        <div class="card-body bg-danger-subtle default-rounded text-center p-4">
            <div class="d-flex flex-column align-items-center">
                <i class="bi bi-exclamation-triangle-fill text-danger fs-1"></i>
                <h5 class="mt-2 text-danger">Password Reset Link Expired</h5>
                <p>Your password reset link has expired.</p>
                <ul class="text-start">
                    <li>You can request a new password reset link.</li>
                    <li>Check your inbox and spam folder after requesting.</li>
                    <li>If you don’t receive an email, please <strong><a href="mailto:<?= $_ENV["MAIL_REPLY_TO_ADDRESS"] ?>">contact support</a></strong>.
                    </li>
                </ul>

                <div class="mt-4">
                    <a href="<?= PUBLIC_URL . "/forgot-password" ?>" class="btn btn-danger default-rounded">Request New Link</a>
                </div>
            </div>
        </div>
    </div>
</div>
