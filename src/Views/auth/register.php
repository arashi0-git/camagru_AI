<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <style>
        .error { color: red; }
    </style>
</head>
<body>
    <h1>Register</h1>
    <?php if (!empty($errors)): ?>
        <div class="error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="/register" method="POST">
        <div>
            <label>Username:</label>
            <input type="text" name="username" value="<?= htmlspecialchars($old['username'] ?? '') ?>">
        </div>
        <div>
            <label>Email:</label>
            <input type="email" name="email" value="<?= htmlspecialchars($old['email'] ?? '') ?>">
        </div>
        <div>
            <label>Password:</label>
            <input type="password" name="password">
        </div>
        <div>
            <label>Confirm Password:</label>
            <input type="password" name="confirm_password">
        </div>
        <button type="submit">Register</button>
    </form>
    <p><a href="/">Back to Home</a></p>
</body>
</html>
