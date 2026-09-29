<?php
session_start();
require_once 'db.php';

//register

if (isset ($_POST['register'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);



    //checks if email exists
    $checkEmail = $conn->query("SELECT email FROM users WHERE email = '$email'");

    if ($checkEmail->num_rows > 0) {

        $_SESSION['register_error'] = 'Email is already registered!';
        $_SESSION['active_form'] = 'register';

    } else {

        $conn->query("
        INSERT INTO users (username, email, password) 
        VALUES ('$username', '$email', '$password')");

        $_SESSION['active_form'] = 'login';
        

    }

    header("Location: Home.php");
    exit();

}




if(isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $result = $conn->query("SELECT * FROM users WHERE email = '$email'");

    if($result->num_rows > 0) {

        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password_hash'])) {

            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];

        }

        // if($user['role'] === 'admin'){
        //     header("Location: admin_page.php");
        // } else {
        //     header("Location: user_page.php");
        // }

        header("Location: ../pages/Home.php");
        exit();
    }

    //login failed
    $_SESSION['login_error'] = 'Incorrect email or password';
    $_SESSION['active_form'] = 'login';

    header("Location: ../pages/login_index.php");
    exit();

}

?>