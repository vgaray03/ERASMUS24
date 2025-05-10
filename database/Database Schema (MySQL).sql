CREATE DATABASE CantStop;
USE CantStop;

CREATE TABLE Users (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL
);

CREATE TABLE Games (
    id_game INT AUTO_INCREMENT PRIMARY KEY,
    id_player1 INT NOT NULL,
    id_player2 INT,
    state ENUM('activo', 'terminado', 'pendiente') DEFAULT 'pendiente',
    begin TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    end TIMESTAMP NULL,
    FOREIGN KEY (id_player1) REFERENCES Users(id_user),
    FOREIGN KEY (id_player2) REFERENCES Users(id_user)
);

CREATE TABLE Moves (
    id_move INT AUTO_INCREMENT PRIMARY KEY,
    id_game INT NOT NULL,
    id_player INT NOT NULL,
    position_x INT NOT NULL,
    position_y INT NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_game) REFERENCES Games(id_game),
    FOREIGN KEY (id_player) REFERENCES Users(id_user)
);

CREATE TABLE Progress (
    id_progress INT AUTO_INCREMENT PRIMARY KEY,
    id_game INT NOT NULL,
    id_player INT NOT NULL,
    column_number INT NOT NULL CHECK (column_number BETWEEN 2 AND 12), -- Números válidos para columnas
    progress INT DEFAULT 0, -- Avance actual en la columna
    FOREIGN KEY (id_game) REFERENCES Games(id_game),
    FOREIGN KEY (id_player) REFERENCES Users(id_user)
);

