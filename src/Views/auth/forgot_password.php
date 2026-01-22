<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?></title>
    <style>.success { color: green; }</style>
</head>
<body>
    <h1>Forgot Password</h1>
    <?php if (isset($success)): ?>
        <p class="success"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>
    <form action="/forgot-password" method="POST">
        <div>
            <label>Email:</label>
            <input type="email" name="email" required>
        </div>
        <button type="submit">Send Reset Link</button>
    </form>
    <p><a href="/login">Back to Login</a></p>
</body>
</html>
