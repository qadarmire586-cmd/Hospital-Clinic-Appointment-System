-- SQL script to add admin user ilyaas with password ilyas8833
-- Run this in phpMyAdmin or MySQL command line

USE hospital_clinic_appointment_system;

-- First, check if the user already exists
-- SELECT * FROM admins WHERE username = 'ilyaas';

-- If the user exists, update the password
-- UPDATE admins SET password = '$2y$10$X9JxJ.V7V.rp/O4Ly.Qt4.z Ey.QQ.q Ny.QQ.qN y.QQ.qN y.QQ.qN.' WHERE username = 'ilyaas';

-- If the user doesn't exist, insert the new admin user
INSERT INTO admins (username, password, email, full_name) 
VALUES ('ilyaas', '$2y$10$X9JxJ.V7V.rp/O4Ly.Qt4.zEy.QQ.qNy.QQ.qNy.QQ.qNy.QQ.qN.', 'ilyaas@hospital.com', 'Ilyaas Administrator');

-- Verify the user was added
SELECT * FROM admins WHERE username = 'ilyaas';