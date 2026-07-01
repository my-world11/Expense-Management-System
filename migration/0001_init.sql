CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL
);

INSERT INTO users(username,password)
VALUES ('admin','admin');

CREATE TABLE IF NOT EXISTS category  (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);

INSERT INTO category(name) VALUES
('Food'),
('Transport'),
('Shopping'),
('Bills'),
('Entertainment'),
('Other');

CREATE TABLE IF NOT EXISTS expense  (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    item VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    details TEXT,
    added_on DATETIME,
    expense_date DATE,
    FOREIGN KEY (category_id) REFERENCES category(id) ON DELETE CASCADE
);