<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?></title>
    <style>.error { color: red; }</style>
</head>
<body>
    <h1>Reset Password</h1>
    <?php if (isset($errors)): ?>
        <ul class="error">
            <?php foreach($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <form action="/reset-password" method="POST">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
        <div>
            <label>New Password:</label>
            <input type="password" name="password" required>
        </div>
        <div>
            <label>Confirm Password:</label>
            <input type="password" name="confirm_password" required>
        </div>
        <button type="submit">Reset Password</button>
    </form>
</body>
</html>
