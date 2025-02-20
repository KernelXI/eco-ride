CREATE DATABASE IF NOT EXISTS eco_ride;

USE eco_ride;

CREATE TABLE IF NOT EXISTS user (
  user_id INT(11) NOT NULL AUTO_INCREMENT,
  nickname VARCHAR(50) NOT NULL,
  email VARCHAR(50) NOT NULL,
  password VARCHAR(255) NOT NULL,
  role VARCHAR(50) NOT NULL DEFAULT 'passager',
  preferences VARCHAR(255) NOT NULL,
  photo BLOB NOT NULL,
  credit INT(11) NOT NULL DEFAULT 20,
  note INT(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (user_id)
);

CREATE TABLE IF NOT EXISTS car (
  car_id INT(11) NOT NULL AUTO_INCREMENT,
  branding VARCHAR(50) NOT NULL,
  model VARCHAR(50) NOT NULL,
  color VARCHAR(50) NOT NULL,
  seats INT(11) NOT NULL,
  plate VARCHAR(50) NOT NULL,
  date_first_plate VARCHAR(50) NOT NULL,
  eco_type VARCHAR(50) NOT NULL,
  user_id INT(11) NOT NULL,
  PRIMARY KEY (car_id),
  FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS ride (
  ride_id INT(11) NOT NULL AUTO_INCREMENT,
  start_address VARCHAR(50) NOT NULL,
  start_city VARCHAR(50) NOT NULL,
  destination_address VARCHAR(50) NOT NULL,
  destination_city VARCHAR(50) NOT NULL,
  start_date VARCHAR(50) NOT NULL,
  start_time VARCHAR(50) NOT NULL,
  destination_date VARCHAR(50) NOT NULL,
  destination_time VARCHAR(50) NOT NULL,
  price INT(11) NOT NULL,
  driver_id INT(11) NOT NULL,
  car_id INT(11) NOT NULL,
  status VARCHAR(50) NOT NULL,
  PRIMARY KEY (ride_id),
  FOREIGN KEY (car_id) REFERENCES car(car_id) ON DELETE CASCADE,
  FOREIGN KEY (driver_id) REFERENCES user(user_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS feedback (
  feedback_id INT(11) NOT NULL AUTO_INCREMENT,
  user_id INT(11) NOT NULL,
  ride_id INT(11) NOT NULL,
  note VARCHAR(255) NOT NULL,
  message VARCHAR(255) NOT NULL,
  status VARCHAR(50) NOT NULL,
  PRIMARY KEY (feedback_id),
  FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE,
  FOREIGN KEY (ride_id) REFERENCES ride(ride_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS user_ride (
  id INT(11) NOT NULL AUTO_INCREMENT,
  ride_id INT(11) NOT NULL,
  user_id INT(11) NOT NULL,
  PRIMARY KEY (id),
  FOREIGN KEY (ride_id) REFERENCES ride(ride_id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE
);

INSERT INTO user (nickname, email, password, role, preferences, photo, credit, note) 
SELECT 'admin', '', '$2b$12$Wbt.D5KpGnIzFPpb1OMJneb9IZOVrO/V/M1ZmHOH/ox7oBHmHpJUG', 'admin', '', NULL, 20, 1
WHERE NOT EXISTS (SELECT * FROM user WHERE nickname = 'admin');
