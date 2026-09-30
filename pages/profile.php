<?php

session_start();
require_once '../php/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login_index.php');
    exit();
}

$stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$hiddenPassword = str_repeat('*', 10);

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="../css/ProfileStyle.css">
</head>

<body>

    <main>
        <section class="profile-details">
            <h1>Profile</h1>

            <div class="profile-field">
                <label>Username</label>
                <p><?= htmlspecialchars($user['username']); ?></p>
            </div>

            <div class="profile-field">
                <label>Email</label>
                <p><?= htmlspecialchars($user['email']); ?></p>
            </div>

            <div class="profile-field">
                <label>Password</label>
                <p><?= $hiddenPassword; ?></p>
            </div>

            <form action="../php/login_register.php" method="post">
                <button type="submit" name="logout">Log out</button>
            </form>
        </section>

        <nav class="bottom-nav" aria-label="Main menu">
            <ul>
                <li>
                    <a href="Home.php">
                        <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8" />
                            <path d="M3 10a2 2 0 0 1 .709-1.528l7-6a2 2 0 0 1 2.582 0l7 6A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                        </svg>
                        Home
                    </a>
                </li>
                <li>
                    <a href="profile.php" class="active" aria-current="page">
                        <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                            <circle cx="12" cy="7" r="4" />
                        </svg>
                        Profile
                    </a>
                </li>
                <li>
                    <a href="Insights.php">
                        <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M13 17V9" />
                            <path d="M18 17V5" />
                            <path d="M3 3v16a2 2 0 0 0 2 2h16" />
                            <path d="M8 17v-3" />
                        </svg>
                        Insight
                    </a>
                </li>
                <li>
                    <a href="../Index.php">
                        <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="13" r="7" />
                            <path d="M12 13V9" />
                            <path d="M12 13l3 2" />
                            <path d="M4 6a3 3 0 0 1 4-3" />
                            <path d="M20 6a3 3 0 0 0-4-3" />
                            <path d="M8 20l-1.5 2" />
                            <path d="M16 20l1.5 2" />
                        </svg>
                        Alarm
                    </a>
                </li>
            </ul>
        </nav>
    </main>

    <footer>
    </footer>

</body>

</html>
