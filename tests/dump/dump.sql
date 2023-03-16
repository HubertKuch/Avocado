CREATE
    DATABASE avocado_test_db;

CREATE TABLE users
(
    id       INT   NOT NULL PRIMARY KEY AUTO_INCREMENT,
    username TEXT  NOT NULL,
    password TEXT  NOT NULL,
    amount   FLOAT NOT NULL,
    role     ENUM ('admin', 'user') DEFAULT 'user'
);

CREATE TABLE books
(
    id          INT          NOT NULL PRIMARY KEY AUTO_INCREMENT,
    name        VARCHAR(255) NOT NULL,
    description TEXT         NOT NULL,
    user_id     INT          NOT NULL REFERENCES users (id)
);

INSERT INTO users (username, password, amount, role)
VALUES ('test', 'test', 2.9, 'user');

INSERT INTO books(name, `description`, user_id)
VALUES ('test1', 'test1', (SELECT id FROM users LIMIT 1)),
       ('test2', 'test2', (SELECT id FROM users LIMIT 1)),
       ('test3', 'test3', (SELECT id FROM users LIMIT 1)),
       ('test4', 'test4', (SELECT id FROM users LIMIT 1)),
       ('test5', 'test5', (SELECT id FROM users LIMIT 1)),
       ('test6', 'test6', (SELECT id FROM users LIMIT 1));
