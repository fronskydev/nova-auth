<?php
$data = isset($_POST["data"]) ? json_decode($_POST["data"], true) : [];
?>

<div class="auth-content">
    <div class="text-center">
        <div class="p-3 mb-3">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-fingerprint"
                 viewBox="0 0 16 16">
                <path d="M8.06 6.5a.5.5 0 0 1 .5.5v.776a11.5 11.5 0 0 1-.552 3.519l-1.331 4.14a.5.5 0 0 1-.952-.305l1.33-4.141a10.5 10.5 0 0 0 .504-3.213V7a.5.5 0 0 1 .5-.5Z"/>
                <path d="M6.06 7a2 2 0 1 1 4 0 .5.5 0 1 1-1 0 1 1 0 1 0-2 0v.332q0 .613-.066 1.221A.5.5 0 0 1 6 8.447q.06-.555.06-1.115zm3.509 1a.5.5 0 0 1 .487.513 11.5 11.5 0 0 1-.587 3.339l-1.266 3.8a.5.5 0 0 1-.949-.317l1.267-3.8a10.5 10.5 0 0 0 .535-3.048A.5.5 0 0 1 9.569 8m-3.356 2.115a.5.5 0 0 1 .33.626L5.24 14.939a.5.5 0 1 1-.955-.296l1.303-4.199a.5.5 0 0 1 .625-.329"/>
                <path d="M4.759 5.833A3.501 3.501 0 0 1 11.559 7a.5.5 0 0 1-1 0 2.5 2.5 0 0 0-4.857-.833.5.5 0 1 1-.943-.334m.3 1.67a.5.5 0 0 1 .449.546 10.7 10.7 0 0 1-.4 2.031l-1.222 4.072a.5.5 0 1 1-.958-.287L4.15 9.793a9.7 9.7 0 0 0 .363-1.842.5.5 0 0 1 .546-.449Zm6 .647a.5.5 0 0 1 .5.5c0 1.28-.213 2.552-.632 3.762l-1.09 3.145a.5.5 0 0 1-.944-.327l1.089-3.145c.382-1.105.578-2.266.578-3.435a.5.5 0 0 1 .5-.5Z"/>
                <path d="M3.902 4.222a5 5 0 0 1 5.202-2.113.5.5 0 0 1-.208.979 4 4 0 0 0-4.163 1.69.5.5 0 0 1-.831-.556m6.72-.955a.5.5 0 0 1 .705-.052A4.99 4.99 0 0 1 13.059 7v1.5a.5.5 0 1 1-1 0V7a3.99 3.99 0 0 0-1.386-3.028.5.5 0 0 1-.051-.705M3.68 5.842a.5.5 0 0 1 .422.568q-.044.289-.044.59c0 .71-.1 1.417-.298 2.1l-1.14 3.923a.5.5 0 1 1-.96-.279L2.8 8.821A6.5 6.5 0 0 0 3.058 7q0-.375.054-.736a.5.5 0 0 1 .568-.422m8.882 3.66a.5.5 0 0 1 .456.54c-.084 1-.298 1.986-.64 2.934l-.744 2.068a.5.5 0 0 1-.941-.338l.745-2.07a10.5 10.5 0 0 0 .584-2.678.5.5 0 0 1 .54-.456"/>
                <path d="M4.81 1.37A6.5 6.5 0 0 1 14.56 7a.5.5 0 1 1-1 0 5.5 5.5 0 0 0-8.25-4.765.5.5 0 0 1-.5-.865m-.89 1.257a.5.5 0 0 1 .04.706A5.48 5.48 0 0 0 2.56 7a.5.5 0 0 1-1 0c0-1.664.626-3.184 1.655-4.333a.5.5 0 0 1 .706-.04ZM1.915 8.02a.5.5 0 0 1 .346.616l-.779 2.767a.5.5 0 1 1-.962-.27l.778-2.767a.5.5 0 0 1 .617-.346m12.15.481a.5.5 0 0 1 .49.51c-.03 1.499-.161 3.025-.727 4.533l-.07.187a.5.5 0 0 1-.936-.351l.07-.187c.506-1.35.634-2.74.663-4.202a.5.5 0 0 1 .51-.49"/>
            </svg>
        </div>
        <h1 class="title">Create new password</h1>
        <p class="text-body-secondary">Please create a new password below to regain access to your account.</p>
    </div>

    <div class="card border-0">
        <div class="card-body bg-body-tertiary default-rounded">
            <form method="post" action="<?= PUBLIC_URL . "/reset-password/submit" ?>">
                <input type="hidden" name="csrf" value="<?= $_ENV["CSRF_TOKEN"] ?>">
                <input type="hidden" name="auth-key" value="<?= $authKey ?>">
                <div class="mb-3 text-body-secondary">
                    <label for="passwordInput" class="form-label">Password</label>
                    <input type="password" class="form-control default-rounded" id="passwordInput" name="password" value="<?= $data["password"] ?? "" ?>" required>
                </div>
                <div class="mb-3 text-body-secondary">
                    <label for="confirmPasswordInput" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control default-rounded" id="confirmPasswordInput" name="confirm_password" value="<?= $data["confirm_password"] ?? "" ?>" required>
                    <small>Make sure it match with the above password.</small>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary default-rounded">Reset password</button>
                </div>
            </form>

            <?php if (isset($_POST["ERROR"])) { ?>
                <p class="mt-3 mb-0 text-danger text-center">
                    <?= $_POST["ERROR"] ?>
                </p>
            <?php } ?>
        </div>
    </div>

    <div class="mt-3 text-center">
        <div class="small text-body-tertiary">
            Copyright &copy; <?= date("Y") . " " . ucwords(str_replace(['-', '_'], ' ', $_ENV["APP_NAME"]))?>.
        </div>
    </div>
</div>
