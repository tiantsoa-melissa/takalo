CREATE DATABASE IF NOT EXISTS takalo;
USE takalo;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL UNIQUE,
    username VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL
);

INSERT INTO users (email, username, password_hash)
VALUES 
('alice@example.com', 'Alice', 'HASH_MDP1'),
('bob@example.com', 'Bob', 'HASH_MDP2');
