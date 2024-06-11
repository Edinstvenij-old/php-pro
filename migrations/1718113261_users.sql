CREATE TABLE users (
                       id INT AUTO_INCREMENT PRIMARY KEY,
                       name VARCHAR(255) NOT NULL,
                       email VARCHAR(255) NOT NULL UNIQUE,
                       age INT(3) NOT NULL
);

INSERT INTO users (name, email, age)
VALUES
    ('Denys', 'admin@gmail.com', 25),
    ('Den', 'min@gmail.com', 21),
    ('Nik', 'min@gmail.com', 30),
    ('Vasiliy', 'vasiliy@gmail.com', 18);
