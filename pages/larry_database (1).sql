-- Larry lamp web app - database schema
-- Import in phpMyAdmin: create a database (e.g. "larry"), select it, go to the "Import" tab, choose this file.

-- Lets you re-import this file. WARNING: this deletes all existing data in these tables,
-- so remove these 4 lines once your app has real data.
DROP TABLE IF EXISTS sleep_logs;
DROP TABLE IF EXISTS alarms;
DROP TABLE IF EXISTS settings;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(50)  NOT NULL UNIQUE,
    email         VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,          -- use PHP password_hash(), NEVER store the plain password
    sleep_goal_minutes INT NOT NULL DEFAULT 480,  -- the "8h goal" ring on the insights page
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Settings page: one row per user
CREATE TABLE settings (
    user_id            INT PRIMARY KEY,
    light_brightness   TINYINT NOT NULL DEFAULT 70,   -- 0-100 (slider)
    light_warmth       TINYINT NOT NULL DEFAULT 65,   -- 0-100 (slider)
    gentle_wake_up     BOOLEAN NOT NULL DEFAULT TRUE, -- toggle
    sound_enabled      BOOLEAN NOT NULL DEFAULT TRUE, -- toggle
    energy_boost_mode  BOOLEAN NOT NULL DEFAULT FALSE,-- toggle
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Home + Clock page: the wake-up alarms
CREATE TABLE alarms (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    user_id          INT NOT NULL,
    wake_time        TIME NOT NULL,                    -- e.g. 07:00:00
    days_of_week     VARCHAR(30) NOT NULL DEFAULT 'Mon,Tue,Wed,Thu,Fri,Sat,Sun',
    light_start_minutes_before INT NOT NULL DEFAULT 30, -- light begins X min before wake time
    start_intensity  TINYINT NOT NULL DEFAULT 80,
    end_intensity    TINYINT NOT NULL DEFAULT 100,
    sound_name       VARCHAR(50) DEFAULT 'Gentle birds',
    is_active        BOOLEAN NOT NULL DEFAULT TRUE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insights page: one row per night
CREATE TABLE sleep_logs (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT NOT NULL,
    sleep_date    DATE NOT NULL,          -- the morning you woke up
    bed_time      DATETIME,
    wake_time     DATETIME,
    total_minutes INT NOT NULL,           -- 7h 48m = 468
    awake_minutes INT DEFAULT 0,          -- sleep stages chart
    rem_minutes   INT DEFAULT 0,
    light_minutes INT DEFAULT 0,
    deep_minutes  INT DEFAULT 0,
    UNIQUE (user_id, sleep_date),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
