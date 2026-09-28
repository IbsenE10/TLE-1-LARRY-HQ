<?php

// PART 1 – PHP: get the data from the database

session_start();
require __DIR__ . '/../php/db.php';

// No login yet → use the demo user (id 1). Once login works, it sets $_SESSION['user_id'].
$userId = $_SESSION['user_id'] ?? 1;

// 7h 48m style text from minutes (468 → "7h 48m")
function formatMinutes($minutes) {
    $hours = intdiv($minutes, 60);
    $mins  = $minutes % 60;
    return $hours > 0 ? "{$hours}h " . str_pad($mins, 2, '0', STR_PAD_LEFT) . 'm' : "{$mins} min";
}

// Runs a query with the user id filled in safely (prepared statement) and returns the result
function queryForUser($conn, $sql, $userId) {
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $userId);   // 'i' = the ? is an integer
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

// ---- 1. The user (for the sleep goal) ----
$result = queryForUser($conn, 'SELECT username, sleep_goal_minutes FROM users WHERE id = ?', $userId);
$user   = mysqli_fetch_assoc($result);
$goal   = (int) $user['sleep_goal_minutes'];             // e.g. 480

// ---- 2. Last night = the newest sleep_logs row ----
$result    = queryForUser($conn, 'SELECT * FROM sleep_logs WHERE user_id = ? ORDER BY sleep_date DESC LIMIT 1', $userId);
$lastNight = mysqli_fetch_assoc($result);                // null if there is no data yet

// ---- 3. The last 7 nights for the chart (oldest first) ----
$result = queryForUser($conn, 'SELECT * FROM (
                                   SELECT * FROM sleep_logs WHERE user_id = ? ORDER BY sleep_date DESC LIMIT 7
                               ) AS week ORDER BY sleep_date ASC', $userId);
$week   = mysqli_fetch_all($result, MYSQLI_ASSOC);

// ---- 4. Active alarms → find the next one that will go off ----
$result = queryForUser($conn, 'SELECT * FROM alarms WHERE user_id = ? AND is_active = TRUE', $userId);
$alarms = mysqli_fetch_all($result, MYSQLI_ASSOC);

$nextAlarm = null;
$nextAlarmTime = null;                                   // timestamp of the next wake-up
for ($day = 0; $day <= 7 && !$nextAlarm; $day++) {       // look at today, tomorrow, ...
    $date    = strtotime("+$day day");
    $dayName = date('D', $date);                         // "Mon", "Tue", ...
    foreach ($alarms as $alarm) {
        $time = strtotime(date('Y-m-d', $date) . ' ' . $alarm['wake_time']);
        $runsToday = in_array($dayName, explode(',', $alarm['days_of_week']));
        if ($runsToday && $time > time() && ($nextAlarmTime === null || $time < $nextAlarmTime)) {
            $nextAlarm = $alarm;
            $nextAlarmTime = $time;
        }
    }
}


// PART 2 – PHP: turn the data into what the page shows

// Total sleep + ring
$total   = $lastNight ? $lastNight['total_minutes'] : 0;
$percent = min(100, round($total / $goal * 100, 1));     // ring fill, max 100%

// "Good sleep" = within 1 hour of the goal
$rangeMin = ($goal - 60) / 60;                           // 480 → 7
$rangeMax = ($goal + 60) / 60;                           // 480 → 9
if ($total >= $goal - 60 && $total <= $goal + 60) {
    $title = "You're doing great!";
    $badge = '✓ Good sleep!';
    $badgeClass = '';
    $message = "You're within your ideal range ({$rangeMin}–{$rangeMax} hours).";
} elseif ($total < $goal - 60) {
    $title = "Let's rest more tonight";
    $badge = '! Too short';
    $badgeClass = 'warn';
    $message = "Try to reach {$rangeMin}–{$rangeMax} hours tonight.";
} else {
    $title = 'You slept in!';
    $badge = '! A bit long';
    $badgeClass = 'warn';
    $message = "A little over your {$rangeMin}–{$rangeMax} hour range.";
}

// Stage legend for last night (percent of the whole night incl. awake)
$stages = [];
if ($lastNight) {
    $inBed = $lastNight['total_minutes'] + $lastNight['awake_minutes'];
    foreach (['awake' => 'Awake', 'rem' => 'REM', 'light' => 'Light', 'deep' => 'Deep'] as $key => $label) {
        $minutes = $lastNight["{$key}_minutes"];
        $stages[] = [
                'key'     => $key,
                'label'   => $label,
                'time'    => formatMinutes($minutes),
                'percent' => round($minutes / $inBed * 100),
        ];
    }
}

// Chart: tallest night = 100% height
$longestNight = 1;
foreach ($week as $night) {
    $longestNight = max($longestNight, $night['total_minutes'] + $night['awake_minutes']);
}

// Next morning text
$lightText = '';
if ($nextAlarm) {
    $lightStart   = $nextAlarmTime - $nextAlarm['light_start_minutes_before'] * 60;
    $minutesUntil = round(($lightStart - time()) / 60);
    if ($minutesUntil <= 0) {
        $lightText = 'Your light is on';
    } elseif ($minutesUntil < 60) {
        $lightText = "Light starts in $minutesUntil minutes";
    } else {
        $lightText = 'Light starts at ' . date('g:i A', $lightStart);
    }
}

// Small helper so text from the database is always safe to print
function e($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}
?>
<!--  PART 3 – HTML: show it -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sleep Summary – GoodmorningLarry!</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/insightsStyle.css">
    <!-- NEW: the JavaScript for this page (defer = run after the HTML has loaded) -->
    <script src="../js/insights.js" defer></script>
</head>
<body>

<!-- TOP BAR  -->
<header class="topbar">
    <a class="logo" href="Home.php">
        <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>
        GoodmorningLarry!
    </a>
    <a class="profile-btn" href="login.php" aria-label="Your profile">
        <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
    </a>
</header>

<main>

    <!--  INTRO  -->
    <section class="hero">
        <div class="hero-text">
            <!-- NEW (idea 4): JS turns this into "Good morning, larry!" etc. -->
            <p class="greeting" id="greeting" data-name="<?= e($user['username']) ?>">Hello!</p>
            <p class="eyebrow">
                <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 5h4"/><path d="M20 3v4"/><path d="M20.985 12.486a9 9 0 1 1-9.473-9.472c.405-.022.617.46.402.803a6 6 0 0 0 8.268 8.268c.344-.215.825-.004.803.401"/></svg>
                Sleep Summary
            </p>
            <h1><?= $lastNight ? $title : 'No sleep data yet' ?></h1>
            <p class="muted">Here's how you slept, and how you feel today.</p>
        </div>
        <img class="larry" src="../images/larry-sleep.jpg" alt="Larry the penguin sleeping">
    </section>

    <?php if ($lastNight): ?>

        <!--  TOTAL SLEEP  (idea 2: JS animates .big-number and .ring) -->
        <section class="card total-card" aria-labelledby="total-title">
            <div class="total-info">
                <h2 class="card-title" id="total-title">
                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 5h4"/><path d="M20 3v4"/><path d="M20.985 12.486a9 9 0 1 1-9.473-9.472c.405-.022.617.46.402.803a6 6 0 0 0 8.268 8.268c.344-.215.825-.004.803.401"/></svg>
                    Total Sleep
                </h2>
                <p class="big-number"><?= formatMinutes($total) ?></p>
                <p class="badge <?= $badgeClass ?>"><?= $badge ?></p>
                <p class="muted small"><?= $message ?></p>
            </div>

            <div class="ring" style="--percent: <?= $percent ?>%;" aria-hidden="true">
                <div class="ring-inner">
                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 5h4"/><path d="M20 3v4"/><path d="M20.985 12.486a9 9 0 1 1-9.473-9.472c.405-.022.617.46.402.803a6 6 0 0 0 8.268 8.268c.344-.215.825-.004.803.401"/></svg>
                    <strong><?= formatMinutes($total) ?></strong>
                    <small>of <?= $goal / 60 ?>h goal</small>
                </div>
            </div>
        </section>

        <!--  SLEEP STAGES  -->
        <section class="card" aria-labelledby="stages-title">
            <header class="card-head">
                <div>
                    <h2 class="card-title" id="stages-title">
                        <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M13 17V9"/><path d="M18 17V5"/><path d="M3 3v16a2 2 0 0 0 2 2h16"/><path d="M8 17v-3"/></svg>
                        Sleep Stages
                    </h2>
                    <p class="muted small">Your last <?= count($week) ?> nights. Tap a night to see its details.</p>
                </div>
                <a href="#" class="chevron" aria-label="More about your sleep stages">›</a>
            </header>

            <figure>
                <!-- One column per night. Each piece's height = minutes in that stage. -->
                <div class="week-chart" role="img" aria-label="Sleep stages for the last <?= count($week) ?> nights">
                    <?php foreach ($week as $night): ?>
                        <!-- NEW (idea 1): the data- attributes give JS this night's numbers -->
                        <div class="night"
                             data-day="<?= date('l', strtotime($night['sleep_date'])) ?>"
                             data-awake="<?= $night['awake_minutes'] ?>"
                             data-rem="<?= $night['rem_minutes'] ?>"
                             data-light="<?= $night['light_minutes'] ?>"
                             data-deep="<?= $night['deep_minutes'] ?>">
                            <div class="night-bar">
                                <?php foreach (['deep', 'light', 'rem', 'awake'] as $stage): ?>
                                    <div class="piece <?= $stage ?>"
                                         style="height: <?= $night["{$stage}_minutes"] / $longestNight * 100 ?>%"></div>
                                <?php endforeach; ?>
                            </div>
                            <span class="night-label"><?= date('D', strtotime($night['sleep_date'])) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <figcaption>
                    <p class="muted small legend-title">Last night</p>
                    <ul class="legend">
                        <?php foreach ($stages as $stage): ?>
                            <li>
                                <span class="dot <?= $stage['key'] ?>"></span>
                                <b><?= $stage['label'] ?></b>
                                <!-- NEW (idea 1): data-stage lets JS find and change this text -->
                                <small data-stage="<?= $stage['key'] ?>"><?= $stage['time'] ?> (<?= $stage['percent'] ?>%)</small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </figcaption>
            </figure>
        </section>

    <?php endif; ?>

    <!--  NEXT MORNING  -->
    <section class="card morning-card" aria-labelledby="morning-title">
        <div class="morning-top">
            <svg class="icon sun" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>
            <div class="morning-info">
                <h2 class="muted" id="morning-title">Your Next Morning</h2>
                <?php if ($nextAlarm): ?>
                    <p class="wake-time">
                        <time datetime="<?= date('Y-m-d H:i', $nextAlarmTime) ?>"><?= date('g:i A', $nextAlarmTime) ?></time>
                    </p>
                    <p class="muted small"><?= $lightText ?> · <?= e($nextAlarm['sound_name']) ?></p>
                <?php else: ?>
                    <p class="wake-time">No alarm set</p>
                <?php endif; ?>
            </div>
        </div>
        <!-- Alarms are edited on their own page now -->
        <a href="Alarm.php" class="pill-btn">View details ›</a>
    </section>

</main>

<!--  BOTTOM MENU  -->
<nav class="bottom-nav" aria-label="Main menu">
    <ul>
        <li>
            <a href="Home.php">
                <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8"/><path d="M3 10a2 2 0 0 1 .709-1.528l7-6a2 2 0 0 1 2.582 0l7 6A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                Home
            </a>
        </li>
        <li>
            <a href="login.php">
                <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                Profile
            </a>
        </li>
        <li>
            <a href="Insights.php" class="active" aria-current="page">
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

</body>
</html>