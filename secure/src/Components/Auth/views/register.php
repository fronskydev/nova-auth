<?php
$data = isset($_POST["data"]) ? json_decode($_POST["data"], true) : [];
?>

<br /><br /><br /><br />

<div class="auth-content">
    <div class="text-center">
        <a href="<?= PUBLIC_URL ?>">
            <img class="logo white-image-dark" src="<?= PUBLIC_URL . "/assets/images/icons/icon.png" ?>" alt="<?= ucwords(str_replace(['-', '_'], ' ', $_ENV["APP_NAME"])) ?> Logo" style="display: inline-block;">
        </a>
        <h1 class="title">Create Account</h1>
        <p class="text-body-secondary">Already have an account? <a class="text-decoration-none" href="<?= PUBLIC_URL . "/login" ?>">Sign in here</a>.
        </p>
    </div>

    <div class="card border-0">
        <div class="card-body bg-body-tertiary default-rounded">
            <form method="post" action="<?= PUBLIC_URL . "/register/submit" ?>">
                <input type="hidden" name="csrf" value="<?= $_ENV["CSRF_TOKEN"] ?>">
                <div class="mb-3 text-body-secondary">
                    <label for="fullNameInput" class="form-label">Full Name</label>
                    <input type="text" class="form-control default-rounded" id="fullNameInput" name="full_name" value="<?= $data["full_name"] ?? "" ?>" required>
                </div>
                <div class="mb-3 text-body-secondary">
                    <label for="usernameInput" class="form-label">Username</label>
                    <input type="text" class="form-control default-rounded" id="usernameInput" name="username" value="<?= $data["username"] ?? "" ?>" required>
                </div>
                <div class="mb-3 text-body-secondary">
                    <label for="emailInput" class="form-label">Email</label>
                    <input type="email" class="form-control default-rounded" id="emailInput" name="email" value="<?= $data["email"] ?? "" ?>" required>
                </div>
                <div class="mb-3 text-body-secondary">
                    <label for="passwordInput" class="form-label">Password</label>
                    <input type="password" class="form-control default-rounded" id="passwordInput" name="password" value="<?= $data["password"] ?? "" ?>" required>
                </div>
                <div class="mb-3 text-body-secondary">
                    <label for="confirmPasswordInput" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control default-rounded" id="confirmPasswordInput" name="confirm_password" value="<?= $data["confirm_password"] ?? "" ?>" required>
                    <small>Make sure it match with the above password.</small>
                </div>
                <div class="form-check mb-3 text-body-secondary">
                    <input class="form-check-input default-rounded" type="checkbox" id="legalCheck" name="legalCheck">
                    <label class="form-check-label" for="legalCheck">
                        <a class="text-decoration-none" href="<?= PUBLIC_URL . "/terms" ?>">Terms of Use</a> & <a class="text-decoration-none" href="<?= PUBLIC_URL . "/privacy" ?>">Privacy Policy</a> accepted.
                    </label>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary default-rounded">Sign up</button>
                </div>

                <?php if (isset($_POST["ERROR"])) { ?>
                    <p class="mt-3 mb-0 text-danger text-center">
                        <?= $_POST["ERROR"] ?>
                    </p>
                <?php } ?>

                <?php if ($_ENV["FRONSKY_API_ENABLED"] == "true") { ?>
                    <div class="d-grid mt-3">
                        <a href="<?= PUBLIC_URL . "/api/login" ?>" class="btn btn-secondary default-rounded">
                            Continue with Fronsky®
                        </a>
                    </div>
                <?php } ?>
            </form>
        </div>
    </div>

    <div class="mt-3 text-center">
        <div class="small text-body-tertiary">
            Copyright &copy; <?= date("Y") . " " . ucwords(str_replace(['-', '_'], ' ', $_ENV["APP_NAME"]))?>.
        </div>
    </div>
</div>
