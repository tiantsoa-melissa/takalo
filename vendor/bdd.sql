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

-- Table Catégorie
CREATE TABLE categorie (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL
);

-- Table Objets
CREATE TABLE objets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    description TEXT,
    photo VARCHAR(255),
    proprietaire INT NOT NULL,
    categorie_id INT,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('disponible', 'echange_en_cours', 'echange') DEFAULT 'disponible',
    FOREIGN KEY (proprietaire) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (categorie_id) REFERENCES categorie(id) ON DELETE SET NULL
);

-- Table pour les demandes d'échange
CREATE TABLE demandes_echange (
    id INT AUTO_INCREMENT PRIMARY KEY,
    objet_propose INT NOT NULL,
    objet_demande INT NOT NULL,
    demandeur INT NOT NULL,
    proprietaire INT NOT NULL,
    status ENUM('en_attente', 'accepte', 'refuse') DEFAULT 'en_attente',
    message TEXT,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (objet_propose) REFERENCES objets(id) ON DELETE CASCADE,
    FOREIGN KEY (objet_demande) REFERENCES objets(id) ON DELETE CASCADE,
    FOREIGN KEY (demandeur) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (proprietaire) REFERENCES users(id) ON DELETE CASCADE
);

-- Insertion de données d'exemple
INSERT INTO categorie (nom) VALUES 
('Électronique'),
('Vêtements'),
('Livres'),
('Sport'),
('Maison'),
('Jouets');

INSERT INTO objets (nom, description, photo, proprietaire, categorie_id) VALUES
('iPhone 12', 'iPhone 12 en bon état, quelques griffures mineures', 'iphone12.jpg', 1, 1),
('Livre "Le Petit Prince"', 'Livre en parfait état, édition récente', 'petit_prince.jpg', 1, 3),
('Veste en Jean', 'Veste Levis taille M, très peu portée', 'veste_jean.jpg', 2, 2),
('Ballon de Football', 'Ballon de foot Nike, utilisé quelques fois', 'ballon.jpg', 2, 4);
