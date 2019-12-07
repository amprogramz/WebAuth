DROP DATABASE IF EXISTS user_database;
CREATE DATABASE user_database;
USE user_database;

CREATE TABLE user_auth (
    user_id     INT     NOT NULL   PRIMARY KEY,
    user_password    VARCHAR(500)    NOT NULL,
    salt        VARCHAR(500)    NOT NULL,
    pass_key    VARCHAR(500)    NOT NULL,
    hash_key    VARCHAR(500)    NOT NULL
);

CREATE TABLE user_info (

    user_id     INT     NOT NULL   PRIMARY KEY      AUTO_INCREMENT,
    user_email  VARCHAR(255)    NOT NULL	unique,
    user_name   VARCHAR(255)    NOT NULL 	unique,
    user_first_name VARCHAR(255)    NOT NULL,
    user_last_name VARCHAR(255)    NOT NULL,
    date_of_birth VARCHAR(255)    NOT NULL
);

INSERT INTO user_auth (user_id, user_password, salt, pass_key, hash_key) VALUES
(1, '968bc40c40b4997b883899c530457397', '32fa675daf83e38a63eba70cebbd3096', '01211323033131311', '01121022320332'),
(2, '04c9052458e3e45207ed4de93e0003b56264c609', 'salt', '1', '1');

INSERT INTO user_info (user_id, user_email, user_name, user_first_name, user_last_name, date_of_birth) VALUES
(1, 'user1@chatspace.com', 'user1', 'User', 'One', '12/06/19'),
(2, 'm@m.m', 'MnM', 'M', 'M', '11/12/13');


/*create user 'dave'@'localhost';
grant select, update, insert, delete on * to 'dave'@'localhost';
*/