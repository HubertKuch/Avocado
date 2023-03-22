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

CREATE TABLE book_details
(
    id         INT NOT NULL PRIMARY KEY REFERENCES books (id),
    written_at TIMESTAMP,
    added_at   TIMESTAMP
);

CREATE TABLE genre
(
    id   INT          NOT NULL PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL
);

CREATE TABLE book_genre
(
    genre_id INT NOT NULL REFERENCES genre (id),
    book_id  INT NOT NULL REFERENCES books (id)
);

INSERT INTO users (id, username, password, amount, role)
VALUES (1, 'John', 'Doe', 2.9, 'user');

INSERT INTO genre (id, name)
VALUES (1, 'Fantasy'),
       (2, 'Historical'),
       (3, 'Biography'),
       (4, 'Horror');

INSERT INTO books(id, name, `description`, user_id)
VALUES (1, 'Lord Of The Rings', 'Lorem ipsum', 1),
       (2, 'Metro 2033', 'Lorem ipsum', 1),
       (3, 'Witcher', 'Lorem ipsum', 1),
       (4, 'Koralina', 'Lorem ipsum', 1),
       (5, 'The Hobbit', 'Lorem ipsum', 1),
       (6, 'Barack Obama', 'Lorem ipsum', 1);

INSERT INTO book_genre
VALUES (1, 1),
       (4, 2),
       (3, 1),
       (4, 4),
       (5, 1),
       (6, 3);

INSERT INTO book_details VALUES
                             (1, current_timestamp(), current_timestamp()),
                             (2, current_timestamp(), current_timestamp()),
                             (3, current_timestamp(), current_timestamp()),
                             (4, current_timestamp(), current_timestamp()),
                             (5, current_timestamp(), current_timestamp()),
                             (6, current_timestamp(), current_timestamp());
