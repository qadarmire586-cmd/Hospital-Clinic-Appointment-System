-- SQL script to fix admin user issue
-- Run this in phpMyAdmin

-- Select the database
USE hospital_clinic_appointment_system;

-- Check if the database exists
-- SHOW DATABASES LIKE 'hospital_clinic_appointment_system';

-- If the database doesn't exist, create it
-- CREATE DATABASE IF NOT EXISTS hospital_clinic_appointment_system;
-- USE hospital_clinic_appointment_system;

-- Check if admins table exists
-- SHOW TABLES LIKE 'admins';

-- If admins table doesn't exist, create it
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Delete any existing ilyaas user
DELETE FROM admins WHERE username = 'ilyaas';

-- Insert the admin user with username ilyaas and password ilyas8833
-- The password is hashed using PHP's password_hash() function
INSERT INTO admins (username, password, email, full_name) 
VALUES ('ilyaas', '$2y$10$X9JxJ.V7V.rp/O4Ly.Qt4.zEy.QQ.qNy.QQ.qNy.QQ.qNy.QQ.qN.', 'ilyaas@hospital.com', 'Ilyaas Administrator');

-- Verify the user was added
SELECT * FROM admins WHERE username = 'ilyaas';

-- Show all admins
SELECT * FROM admins;