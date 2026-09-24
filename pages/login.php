<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
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
        <div class="form-box active" id="login-form">
            <form action="login_register.php" method="post">
                <h2>Login</h2>
                <div class="form-fields">
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <button type="submit" name="login">Login</button>
                    <p>Don't have an account? <a href="#" onclick="showForm('register-form')">Register</a></p>
                </div>

            </form>
        </div>

        <!-- register -->

        <div class="form-box" id="register-form">
            <form action="login_register.php" method="post">
                <h2>Register</h2>
                <input type="text" name="Name" placeholder="Name" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="register">Register</button>
                <p>Already have an account? <a href="#" onclick="showForm('login-form')">Login</a></p>
            </form>
        </div>
    </div>

    <script src="../js/login.js"></script>

</body>

</html>