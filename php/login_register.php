<?php

session_start();
require_once 'db.php';


// ==========================
// REGISTER
// ==========================

if (isset($_POST['register'])) {

    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);


    // Check of email al bestaat
    $checkEmail = $conn->query(
        "SELECT email FROM users WHERE email = '$email'"
    );


    if ($checkEmail->num_rows > 0) {

        // Email bestaat al
        $_SESSION['register_error'] = 'Email is already registered!';
        $_SESSION['active_form'] = 'register';
        header('Location: ../pages/login_index.php');
        exit();

    }


    // Nieuwe gebruiker toevoegen
    $conn->query(
        "INSERT INTO users (username, email, password_hash)
        VALUES ('$username', '$email', '$password')"
    );


    // Meteen inloggen na registratie
    $_SESSION['username'] = $username;
    $_SESSION['email'] = $email;


    header("Location: ../pages/Home.php");
    exit();
}



// ==========================
// LOGIN
// ==========================

if (isset($_POST['login'])) {

    $email = $_POST['email'];
    $password = $_POST['password'];


    // Gebruiker zoeken
    $result = $conn->query(
        "SELECT * FROM users WHERE email = '$email'"
    );


    if ($result->num_rows > 0) {

        $user = $result->fetch_assoc();


        // Wachtwoord controleren
        if (password_verify($password, $user['password_hash'])) {
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];

            header("Location: ../pages/Home.php");
            exit();
        }
    }


    // Login mislukt
    $_SESSION['login_error'] = 'Incorrect email or password';
    $_SESSION['active_form'] = 'login';
    header('Location: ../pages/login_index.php');
    exit();
}
?>