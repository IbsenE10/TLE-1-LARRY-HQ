<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TLE1</title>
    <link rel="stylesheet" href="../css/HomeStyle.css">
    <script src="../js/home.js" defer></script>
</head>

<body>

    <header>
        <div>
            <h1></h1>
        </div>
    </header>

    <main>
        <section class="subheading1">
            <div class="discription">
                <h2>"Goodmorning Larry!"</h2>
                <p>
                    Larry is happy to see you!
                    Hopefully you woke up calm and were able to have an energetic start!
                    Larry noticed you had a lot of sugar before bed yesterday, this can cause a disruption in sleep.
                    Lets dile back on the sugar today and start fresh so we can end smoothly!
                </p>
            </div>
            <div class="Larry" id="larry-waving">
                <video autoplay loop muted playsinline>
                    <source src="../images/LarryWaving.webm" type="video/webm">
                </video>
            </div>
        </section>

        <nav class="bottom-nav" aria-label="Main menu">
            <ul>
                <li>
                    <a href="Home.php" class="active" aria-current="page">
                        <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8" />
                            <path d="M3 10a2 2 0 0 1 .709-1.528l7-6a2 2 0 0 1 2.582 0l7 6A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                        </svg>
                        Home
                    </a>
                </li>
                <li>
                    <a href="login_index.php">
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

</html>