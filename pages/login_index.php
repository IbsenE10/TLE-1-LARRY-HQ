<?php

session_start();

$errors = [
    'login' => $_SESSION['login_error'] ?? '',
    'register' => $_SESSION['register_error'] ?? ''
];
$activeForm = $_SESSION['active_form'] ?? 'login';

unset($_SESSION['login_error'], $_SESSION['register_error'], $_SESSION['active_form']);

function showError($error){
    return !empty($error) ? "<p class='error-message'>$error</p>" : '';
}

function isActiveForm($formName, $activeForm){
    return $formName === $activeForm ? 'active' : '';
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log in</title>
    <link rel="stylesheet" href="../css/login-register.css">
</head>

<header>
    <!-- <nav>
        <div>logo</div>
        <div id="empty"></div>
        <a herf="#first-example"> Login</a>
    </nav> -->

    <!-- <img src="../goodMorningLogo.png"> -->
</header>

<body>
    <!-- log in -->

    <div class="container">
        <div class="form-box active" <?= isActiveForm('login', $activeForm); ?> id="login-form">
            <form action="../php/login_register.php" method="post">
                <h2>Login</h2>
                <?= showError($errors ['login']);  ?>
                <div class="form-fields">
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <button type="submit" name="login">Login</button>
                    <p>Don't have an account? <a href="#" onclick="showForm('register-form')">Register</a></p>
                </div>

            </form>
        </div>

        <!-- register -->

        <div class="form-box" <?= isActiveForm('register', $activeForm); ?> id="register-form">
            <form action="../php/login_register.php" method="post">
                <h2>Register</h2>
                <?= showError($errors ['register']);  ?>
                <div class="form-fields">
                    <input type="text" name="username" placeholder="Username" required>
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <button type="submit" name="register">Register</button>
                    <p>Already have an account? <a href="#" onclick="showForm('login-form')">Login</a></p>
                </div>
            </form>
        </div>
    </div>

    <nav class="bottom-nav" aria-label="Main menu">
        <ul>
            <li>
                <a href="Home.php">
                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8"/><path d="M3 10a2 2 0 0 1 .709-1.528l7-6a2 2 0 0 1 2.582 0l7 6A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                    Home
                </a>
            </li>
            <li>
                <a href="login_index.php"  class="active" aria-current="page">
                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    Profile
                </a>
            </li>
            <li>
                <a href="Insights.php">
                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M13 17V9"/><path d="M18 17V5"/><path d="M3 3v16a2 2 0 0 0 2 2h16"/><path d="M8 17v-3"/></svg>
                    Insight
                </a>
            </li>
            <li>
                <a href="../Index.php">
                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="13" r="7"/><path d="M12 13V9"/><path d="M12 13l3 2"/><path d="M4 6a3 3 0 0 1 4-3"/><path d="M20 6a3 3 0 0 0-4-3"/><path d="M8 20l-1.5 2"/><path d="M16 20l1.5 2"/></svg>
                    Alarm
                </a>
            </li>
        </ul>
    </nav>

    <script src="../js/login.js"></script>

</body>

</html>