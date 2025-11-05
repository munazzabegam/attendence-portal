-- Database: attendance_portal_db
-- --------------------------------------------------------

--
-- Table structure for table `users`
-- Roles: 1=Admin, 2=Teacher, 3=Student
--

CREATE TABLE `users` (
  `user_id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(100) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL, -- Stores secure hashed passwords
  `role_id` INT(11) NOT NULL,             -- 1=Admin, 2=Teacher, 3=Student
  `full_name` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

--
-- Table structure for table `classes`
--
CREATE TABLE `classes` (
  `class_id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `class_name` VARCHAR(150) NOT NULL,
  `teacher_id` INT(11),
  FOREIGN KEY (`teacher_id`) REFERENCES `users`(`user_id`)
);

--
-- Table structure for table `enrollment`
-- Links students to classes
--
CREATE TABLE `enrollment` (
  `enrollment_id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT(11) NOT NULL,
  `class_id` INT(11) NOT NULL,
  FOREIGN KEY (`student_id`) REFERENCES `users`(`user_id`),
  FOREIGN KEY (`class_id`) REFERENCES `classes`(`class_id`),
  UNIQUE KEY `uk_student_class` (`student_id`, `class_id`)
);

--
-- Table structure for table `attendance`
--
CREATE TABLE `attendance` (
  `attendance_id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `enrollment_id` INT(11) NOT NULL,
  `attendance_date` DATE NOT NULL,
  `status` ENUM('Present', 'Absent', 'Late', 'Excused') NOT NULL DEFAULT 'Absent',
  `recorded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`enrollment_id`) REFERENCES `enrollment`(`enrollment_id`),
  UNIQUE KEY `uk_attendance_date` (`enrollment_id`, `attendance_date`)
);

--
-- Sample Data (All passwords are 'password'. Hash provided is for 'password'.)
-- Password: password
--
INSERT INTO `users` (`username`, `password_hash`, `role_id`, `full_name`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'System Administrator'),
('prof.smith', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, 'Professor Smith'),
('student.alice', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 'Alice Johnson');

INSERT INTO `classes` (`class_name`, `teacher_id`) VALUES
('CS 101: Intro to Programming', 2);

INSERT INTO `enrollment` (`student_id`, `class_id`) VALUES
(3, 1);