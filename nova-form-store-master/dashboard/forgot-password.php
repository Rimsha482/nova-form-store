<?php
require_once __DIR__ . "/settings/session_start.php";
require_once __DIR__ . "/settings/db.php";

$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['errors']);

$success = $_SESSION['success'] ?? '';
unset($_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Admin Password | NOVA FORM</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body style="background: #0f172a; min-height: 100vh; display: flex; align-items: center; justify-content: center;">
  <div class="container py-5">
    <div class="admin-auth-box text-center mx-auto" style="max-width: 440px; background: #ffffff; padding: 2.5rem; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
      <h2 class="fw-bold mb-2">Password Recovery</h2>
      <p class="text-muted small mb-4">Enter your administrative email to reset your security credentials.</p>

      <?php if (!empty($errors)) : ?>
        <div class="alert alert-danger text-start small mb-3">
          <ul class="mb-0 ps-3">
            <?php foreach ($errors as $error) : ?>
              <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <?php if (!empty($success)) : ?>
        <div class="alert alert-success text-start small mb-3">
          <?= $success ?>
        </div>
      <?php endif; ?>

      <form action="actions/forgot_password_action.php" method="POST" class="text-start">
        <div class="mb-3">
          <label class="form-label small fw-bold">Admin Email</label>
          <input type="email" name="email" class="form-control" placeholder="admin@novashoes.pk" required>
        </div>
        <button type="submit" class="btn btn-dark w-100 py-2 mb-3">Reset Password</button>
        <div class="text-center small"><a href="login.php" class="text-muted text-decoration-none">&larr; Back to Login</a></div>
      </form>
    </div>
  </div>
</body>
</html>
