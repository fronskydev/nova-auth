<?php
use src\Components\Auth\AuthComponent;
$data = isset($_POST["data"]) ? json_decode($_POST["data"], true) : [];
?>

<div class="auth-content">
    <div class="text-center">
        <a href="<?= PUBLIC_URL ?>">
            <img class="logo" src="<?= PUBLIC_URL . "/assets/images/icons/icon.png" ?>" alt="<?= ucwords(str_replace(['-', '_'], ' ', $_ENV["APP_NAME"])) ?> Logo" style="display: inline-block;">
        </a>
        <h1 class="title">Welcome Back</h1>
        <?php if (AuthComponent::getSettings()["register.enabled"]) { ?>
            <p class="text-body-secondary">Don’t have an account yet? <a class="text-decoration-none" href="<?= PUBLIC_URL . "/register" ?>">Register here</a>.</p>
        <?php } ?>
    </div>

    <div class="card border-0">
        <div class="card-body bg-body-tertiary default-rounded">
            <form method="post" action="<?= PUBLIC_URL . "/login/submit" ?>">
                <input type="hidden" name="csrf" value="<?= $_ENV["CSRF_TOKEN"] ?>">
                <div class="mb-3 text-body-secondary">
                    <label for="uidInput" class="form-label">Username / Email</label>
                    <input type="text" class="form-control default-rounded" id="uidInput" name="uid" value="<?= $data["uid"] ?? "" ?>" required>
                </div>
                <div class="mb-3 text-body-secondary">
                    <label for="passwordInput" class="form-label">Password</label>
                    <input type="password" class="form-control default-rounded" id="passwordInput" name="password" value="<?= $data["password"] ?? "" ?>" required>
                </div>
                <div class="mb-3">
                    <a class="text-decoration-none" href="<?= PUBLIC_URL . "/forgot-password" ?>">Forgot Password?</a>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary default-rounded">Sign in</button>
                </div>
            </form>

            <?php if ($_ENV["FRONSKY_API_ENABLED"] == "true") { ?>
                <div class="d-grid mt-3">
                    <a href="<?= PUBLIC_URL . "/api/login" ?>" class="btn btn-secondary default-rounded">
                        Continue with Fronsky®
                    </a>
                </div>
            <?php } ?>

            <?php if (isset($_POST["ERROR"])) { ?>
                <p class="mt-3 mb-0 text-danger text-center">
                    <?= $_POST["ERROR"] ?>
                </p>
            <?php } if (isset($_POST["ERROR"]) && $_POST["ERROR"] === "Your email address is not verified yet.") { ?>
                <p class="mt-1 mb-0 text-body-secondary text-center fst-italic" style="font-size: 0.9rem;">
                    Send a new verification email? <a class="text-decoration-none" href="<?= PUBLIC_URL . "/resend-verification-email/" . encryptText($data["uid"] ?? "") ?>">Click here</a>.
                </p>
            <?php } ?>
            <?php if (isset($_POST["SUCCESS"])) { ?>
                <p class="mt-3 mb-0 text-success text-center">
                    <?= $_POST["SUCCESS"] ?>
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
