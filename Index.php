<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TLE1</title>
    <link rel="stylesheet" href="css/AlarmStyle.css">
</head>

<body>
    
    <main>
        
        <div class="sleep-page">

            <div class="time-section">

                <div class="time-block">
                    <span class="time-label">SleepTime</span>
                    <div class="time-box">
                        <input type="time" id="sleep-time" name="sleep-time" value="22:30">
                    </div>
                </div>

                <div class="time-block">
                    <span class="time-label">WakeTime</span>
                    <div class="time-box">
                        <input type="time" id="wake-time" name="wake-time" value="07:30">
                    </div>
                </div>

            </div>

            <!-- Hier de lamp -->
            <div class="lamp">
                <img id="lamp-image" src="images/Lamp100.png" alt="lamp">
            </div>

            <div class="percentage-circle">
                100%
            </div>

            <label class="power-switch">
                <input type="checkbox" id="alarm-toggle" checked>
                <span class="slider"></span>
            </label>

        </div>

        <nav class="bottom-nav" aria-label="Main menu">
            <ul>
                <li>
                    <a href="pages/Home.php">
                        <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8"/><path d="M3 10a2 2 0 0 1 .709-1.528l7-6a2 2 0 0 1 2.582 0l7 6A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                        Home
                    </a>
                </li>
                <li>
                    <a href="pages/login.php">
                        <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        Profile
                    </a>
                </li>
                <li>
                    <a href="pages/Insights.php">
                        <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M13 17V9"/><path d="M18 17V5"/><path d="M3 3v16a2 2 0 0 0 2 2h16"/><path d="M8 17v-3"/></svg>
                        Insight
                    </a>
                </li>
                <li>
                    <a href="Index.php" class="active" aria-current="page">
                        <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="13" r="7"/><path d="M12 13V9"/><path d="M12 13l3 2"/><path d="M4 6a3 3 0 0 1 4-3"/><path d="M20 6a3 3 0 0 0-4-3"/><path d="M8 20l-1.5 2"/><path d="M16 20l1.5 2"/></svg>
                        Alarm
                    </a>
                </li>
            </ul>
        </nav>
    </main>

</body>
<script src="js/theme.js"></script>
<script type="module" src="js/Clock/main.js"></script>
</html>