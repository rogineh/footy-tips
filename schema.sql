CREATE DATABASE IF NOT EXISTS footy_tips;
USE footy_tips;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'member') DEFAULT 'member',
    display_name VARCHAR(100),
    is_approved BOOLEAN DEFAULT FALSE,
    has_nrl BOOLEAN DEFAULT FALSE,
    has_afl BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Rounds table
CREATE TABLE IF NOT EXISTS rounds (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sport ENUM('NRL', 'AFL') NOT NULL,
    year INT NOT NULL,
    round_number INT NOT NULL,
    deadline DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Games table
CREATE TABLE IF NOT EXISTS games (
    id INT AUTO_INCREMENT PRIMARY KEY,
    round_id INT NOT NULL,
    home_team VARCHAR(100) NOT NULL,
    away_team VARCHAR(100) NOT NULL,
    game_time DATETIME NOT NULL,
    is_first_game BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (round_id) REFERENCES rounds(id) ON DELETE CASCADE
);

-- Extra Questions table
CREATE TABLE IF NOT EXISTS extra_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    round_id INT NOT NULL,
    question_text VARCHAR(255) NOT NULL,
    question_type ENUM('margin', 'random', 'total_score') NOT NULL,
    scoring_range INT DEFAULT 0,
    FOREIGN KEY (round_id) REFERENCES rounds(id) ON DELETE CASCADE
);

-- Tips table (for games)
CREATE TABLE IF NOT EXISTS tips (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    game_id INT NOT NULL,
    winner_id INT NOT NULL COMMENT '1 for Home, 2 for Away, 0 for Draw',
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE,
    UNIQUE KEY (user_id, game_id)
);

-- Extra Tips table (for questions)
CREATE TABLE IF NOT EXISTS extra_tips (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    extra_question_id INT NOT NULL,
    answer VARCHAR(255) NOT NULL,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (extra_question_id) REFERENCES extra_questions(id) ON DELETE CASCADE,
    UNIQUE KEY (user_id, extra_question_id)
);

-- Game Results table
CREATE TABLE IF NOT EXISTS results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    game_id INT NOT NULL UNIQUE,
    winner_id INT NOT NULL,
    home_score INT DEFAULT NULL,
    away_score INT DEFAULT NULL,
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE
);

-- Extra Results table
CREATE TABLE IF NOT EXISTS extra_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    extra_question_id INT NOT NULL UNIQUE,
    correct_answer VARCHAR(255) NOT NULL,
    FOREIGN KEY (extra_question_id) REFERENCES extra_questions(id) ON DELETE CASCADE
);

-- Insert initial admin user (password will be hashed in PHP, but for now we note it)
-- User: rok, Password: pwd789
-- In a real scenario, we'll hash this.
INSERT INTO users (username, email, password, role, is_approved, has_nrl, has_afl) 
VALUES ('rok', 'admin@example.com', '$2y$10$YourHashedPasswordHere', 'admin', TRUE, TRUE, TRUE);
