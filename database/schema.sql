-- Adatbázis létrehozása
CREATE DATABASE IF NOT EXISTS todoapp CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

USE todoapp;

-- Todos tábla létrehozása
CREATE TABLE IF NOT EXISTS todos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    completed BOOLEAN DEFAULT FALSE,
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    due_date DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_completed (completed),
    INDEX idx_priority (priority),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Minta adatok beszúrása
INSERT INTO todos (title, description, priority, completed, due_date) VALUES
('Docker kornyezet beallitasa', 'Docker kontenerek konfiguralasa az alkalmazashoz', 'high', true, '2025-11-20 18:00:00'),
('CI/CD pipeline implementalasa', 'GitHub Actions workflow letrehozasa automatikus teszteleshez és deployment-hez', 'high', false, '2025-11-25 23:59:59'),
('Prometheus monitoring hozzaadasa', 'Prometheus es Grafana beallitasa alkalmazas monitoringhoz', 'medium', false, '2025-11-28 17:00:00'),
('Dokumentació irasa', 'Atfogo README es projekt dokumentacio keszitese', 'medium', false, '2025-11-30 12:00:00'),
('Kubernetes deployment', 'Alkalmazas telepitese Kubernetes cluster-re', 'low', false, '2025-12-05 15:00:00');
