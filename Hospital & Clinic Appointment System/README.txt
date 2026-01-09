Hospital & Clinic Appointment System
===================================

A web-based application for managing hospital appointments built with PHP, MySQL, HTML, CSS, JavaScript, and Bootstrap.

Features
--------
Patient Module:
- Patient registration and login
- Profile management
- Search doctors by department
- Book appointments
- View appointment history
- Cancel appointments

Admin Module:
- Admin login
- Manage doctors (add/edit/delete)
- Manage doctor schedules
- View, approve, or cancel appointments
- View patient list
- Dashboard analytics

Technology Stack
----------------
- Backend: PHP 7+
- Database: MySQL
- Frontend: HTML, CSS, JavaScript, Bootstrap 5
- Database Management: phpMyAdmin

Setup Instructions
------------------
1. Install XAMPP/WAMP server on your computer
2. Copy all files to your web server directory:
   - For XAMPP: C:\xampp\htdocs\
   - For WAMP: C:\wamp\www\
3. Start Apache and MySQL services
4. Open phpMyAdmin (usually at http://localhost/phpmyadmin)
5. Create a new database named "hospital_clinic_appointment_system"
6. Import the database schema from database/schema.sql file
7. Access the application through your browser at http://localhost/Hospital%20&%20Clinic%20Appointment%20System/

Default Login Credentials
-------------------------
Admin:
- Username: admin
- Password: password

Patient:
- Register a new account to access patient features

Directory Structure
-------------------
├── admin/                 # Admin module files
├── patient/               # Patient module files
├── config/                # Configuration files
├── database/              # Database schema
├── index.php             # Homepage
└── README.txt            # This file

Database Tables
---------------
1. admins                  # Admin users
2. users                   # Patient users
3. doctors                 # Doctor information
4. doctor_schedules        # Doctor availability schedules
5. appointments            # Appointment records

Security Features
-----------------
- Password hashing using PHP's password_hash()
- Prepared statements to prevent SQL injection
- Session management for authentication
- Input validation and sanitization

Customization
-------------
You can customize the system by:
1. Modifying the database schema in database/schema.sql
2. Updating the connection settings in config/db.php
3. Changing the UI design in the respective PHP files
4. Adding new features as needed

Troubleshooting
---------------
If you encounter any issues:
1. Ensure all files are in the correct directory
2. Verify database connection settings in config/db.php
3. Check that Apache and MySQL services are running
4. Make sure the database is imported correctly

License
-------
This project is for educational purposes only.