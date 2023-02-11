CREATE DATABASE avocado_test_db;

CREATE TABLE users
(
    id       INT   NOT NULL PRIMARY KEY AUTO_INCREMENT,
    username TEXT  NOT NULL,
    password TEXT  NOT NULL,
    amount   FLOAT NOT NULL,
    role     ENUM ('admin', 'user') DEFAULT 'user'
);

INSERT INTO users (username, password, amount, role)
VALUES ('test', 'test', 2.9, 'user');

