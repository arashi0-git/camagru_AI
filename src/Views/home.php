<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
</head>
<body>
    <h1><?= $title ?></h1>
    <p>Environment is up and running!</p>
    
    <?php if (isset($_SESSION['user_id'])): ?>
        <p>Welcome, <?= htmlspecialchars($_SESSION['username']) ?>! | <a href="/camera">Camera</a> | <a href="/logout">Logout</a></p>
    <?php else: ?>
        <p><a href="/login">Login</a> | <a href="/register">Register</a></p>
    <?php endif; ?>
</body>
</html>
