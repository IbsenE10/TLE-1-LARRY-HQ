<?php
session_start();
require_once 'db.php';

if (isset($_POST['register'])) {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $emailCheck = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $emailCheck->bind_param('s', $email);
    $emailCheck->execute();
    $emailCheck->store_result();

    if ($emailCheck->num_rows > 0) {
        $_SESSION['register_error'] = 'Email is already registered!';
        $_SESSION['active_form'] = 'register';
        header('Location: ../pages/login_index.php');
        exit();
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $insertUser = $conn->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
    $insertUser->bind_param('sss', $username, $email, $hashedPassword);

    if ($insertUser->execute()) {
        $_SESSION['active_form'] = 'login';
        header('Location: ../pages/login_index.php');
        exit();
    }

    $_SESSION['register_error'] = 'Registration failed. Please try again.';
    $_SESSION['active_form'] = 'register';
    header('Location: ../pages/login_index.php');
    exit();
}

if (isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT username, email, password_hash FROM users WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password_hash'])) {
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            header('Location: ../pages/Home.php');
            exit();
        }
    }

    $_SESSION['login_error'] = 'Incorrect email or password';
    $_SESSION['active_form'] = 'login';
    header('Location: ../pages/login_index.php');
    exit();
}
?>