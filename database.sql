CREATE DATABASE IF NOT EXISTS note_platform;
USE note_platform;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS messages;
DROP TABLE IF EXISTS note_tags;
DROP TABLE IF EXISTS saved_notes;
DROP TABLE IF EXISTS comment_votes;
DROP TABLE IF EXISTS comments;
DROP TABLE IF EXISTS notes;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS subjects;
DROP TABLE IF EXISTS departments;
DROP TABLE IF EXISTS categories;

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    icon VARCHAR(50) DEFAULT 'bi-book',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    department_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    code VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    profile_image VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    pdf_path VARCHAR(255) NOT NULL,
    thumbnail_path VARCHAR(255),
    uploaded_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    note_id INT NOT NULL,
    user_id INT,
    parent_id INT DEFAULT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (note_id) REFERENCES notes(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE comment_votes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    comment_id INT NOT NULL,
    user_id INT NOT NULL,
    vote_type TINYINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_vote (comment_id, user_id),
    FOREIGN KEY (comment_id) REFERENCES comments(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE saved_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    note_id INT NOT NULL,
    saved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_save (user_id, note_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (note_id) REFERENCES notes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE note_tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    note_id INT NOT NULL,
    tag VARCHAR(50) NOT NULL,
    FOREIGN KEY (note_id) REFERENCES notes(id) ON DELETE CASCADE,
    UNIQUE KEY unique_note_tag (note_id, tag)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data for `users`
INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `profile_image`, `created_at`) VALUES
('1', 'admin', 'admin@studymate.com', '$2y$12$Q1wAjBa7OhlDGRgvocv5ie801v1sDztjwFMBM1M7nwL6l4xkAOECy', 'admin', NULL, '2026-09-23 17:41:32'),
('2', 'Thisul', 'thisul@gmail.com', '$2y$10$4dKoNUJHHCr9eRUAbQE2We3kQyT6LH9Pe8m17L5mec7ubsryNpEHe', 'user', NULL, '2026-09-24 19:39:08');

-- Data for `categories`
INSERT INTO `categories` (`id`, `name`, `description`, `icon`, `created_at`) VALUES
('10', 'Social Science and Humanities', '', 'bi-book', '2026-09-23 22:59:28'),
('11', 'Agriculture', '', 'bi-book', '2026-09-23 23:49:14'),
('8', 'Applied Sciences', '', 'bi-book', '2026-09-23 22:56:02'),
('9', 'Medical and Allied Sciences', '', 'bi-activity', '2026-09-23 22:58:13'),
('6', 'Technology', '', 'bi-robot', '2026-09-23 22:53:47'),
('7', 'Management Studies', '', 'bi-graph-up', '2026-09-23 22:55:03');

-- Data for `departments`
INSERT INTO `departments` (`id`, `category_id`, `name`, `description`, `created_at`) VALUES
('1', '1', 'Software Engineering', 'Software development, design patterns, and project management', '2026-09-23 17:41:32'),
('2', '1', 'Computer Science', 'Algorithms, data structures, AI, and machine learning', '2026-09-23 17:41:32'),
('3', '1', 'Information Technology', 'Networking, databases, and system administration', '2026-09-23 17:41:32'),
('4', '2', 'Civil Engineering', 'Structural analysis, construction, and project management', '2026-09-23 17:41:32'),
('5', '2', 'Mechanical Engineering', 'Thermodynamics, fluid mechanics, and manufacturing', '2026-09-23 17:41:32'),
('6', '3', 'Physics', 'Classical mechanics, quantum physics, and electromagnetism', '2026-09-23 17:41:32'),
('7', '3', 'Mathematics', 'Calculus, linear algebra, statistics, and discrete math', '2026-09-23 17:41:32'),
('8', '4', 'Business Administration', 'Management principles, organizational behavior', '2026-09-23 17:41:32'),
('9', '4', 'Accounting', 'Financial accounting, auditing, and taxation', '2026-09-23 17:41:32'),
('10', '6', 'Material Technology', '', '2026-09-23 23:41:56'),
('11', '6', 'Electrical and Electronic Technology', '', '2026-09-23 23:42:49'),
('12', '6', 'Food Technology', '', '2026-09-23 23:43:17'),
('13', '6', 'Information Communication Technology', '', '2026-09-23 23:44:25'),
('14', '6', 'Bioprocess Technology', '', '2026-09-23 23:44:57'),
('15', '11', 'Agricultural Engineering and Soil Science', '', '2026-09-23 23:49:55'),
('16', '11', 'Agricultural Systems', '', '2026-09-23 23:50:17'),
('17', '11', 'Animal and Food Sciences', '', '2026-09-23 23:50:30'),
('18', '11', 'Plant Sciences', '', '2026-09-23 23:50:44'),
('19', '8', 'Biological Sciences', '', '2026-09-23 23:53:27'),
('20', '8', 'chemical sciences', '', '2026-09-23 23:53:57'),
('21', '8', 'Computing', '', '2026-09-23 23:55:04'),
('22', '8', 'Health Promotion', '', '2026-09-23 23:55:24'),
('23', '8', 'Physical Sciences', '', '2026-09-23 23:55:48'),
('24', '7', 'Accountancy and Finance', '', '2026-09-23 23:56:39'),
('25', '7', 'Business Management', '', '2026-09-23 23:57:07'),
('26', '7', 'Human Resource Management', '', '2026-09-23 23:57:40'),
('27', '7', 'Information Systems', '', '2026-09-23 23:58:08'),
('28', '7', 'Marketing Management', '', '2026-09-23 23:58:31'),
('29', '10', 'Archaeology and Heritage Management', '', '2026-09-23 23:59:41'),
('30', '10', 'Economics', '', '2026-09-24 00:00:07'),
('31', '10', 'Environmental Management', '', '2026-09-24 00:00:38'),
('32', '10', 'Humanities', '', '2026-09-24 00:01:06'),
('33', '10', 'Languages', '', '2026-09-24 00:01:29'),
('34', '10', 'Social Sciences', '', '2026-09-24 00:01:53');

-- Data for `subjects`
INSERT INTO `subjects` (`id`, `department_id`, `name`, `code`, `created_at`) VALUES
('1', '1', 'Web Technologies', 'ICT2209', '2026-09-23 17:41:32'),
('2', '1', 'Software Design & Architecture', 'ICT3301', '2026-09-23 17:41:32'),
('3', '1', 'Mobile Application Development', 'ICT3305', '2026-09-23 17:41:32'),
('4', '2', 'Data Structures & Algorithms', 'CS2201', '2026-09-23 17:41:32'),
('5', '2', 'Artificial Intelligence', 'CS3302', '2026-09-23 17:41:32'),
('6', '2', 'Machine Learning', 'CS4401', '2026-09-23 17:41:32'),
('7', '3', 'Computer Networks', 'ICT2203', '2026-09-23 17:41:32'),
('8', '3', 'Database Management Systems', 'ICT2205', '2026-09-23 17:41:32'),
('9', '3', 'Operating Systems', 'ICT3304', '2026-09-23 17:41:32'),
('10', '4', 'Structural Analysis', 'CE3301', '2026-09-23 17:41:32'),
('11', '4', 'Surveying', 'CE2201', '2026-09-23 17:41:32'),
('12', '5', 'Thermodynamics', 'ME3301', '2026-09-23 17:41:32'),
('13', '5', 'Fluid Mechanics', 'ME3302', '2026-09-23 17:41:32'),
('14', '6', 'Classical Mechanics', 'PH2201', '2026-09-23 17:41:32'),
('15', '6', 'Electromagnetism', 'PH3301', '2026-09-23 17:41:32'),
('16', '7', 'Calculus II', 'MA2201', '2026-09-23 17:41:32'),
('17', '7', 'Linear Algebra', 'MA2202', '2026-09-23 17:41:32'),
('18', '8', 'Principles of Management', 'BA2201', '2026-09-23 17:41:32'),
('19', '9', 'Financial Accounting', 'AC2201', '2026-09-23 17:41:32'),
('20', '10', 'Introduction to Material', 'MTT001', '2026-09-24 19:59:47'),
('21', '10', 'Introduction to Polymer', 'MTT002', '2026-09-24 20:00:58'),
('22', '10', 'Introduction to Ceramic', 'MTT003', '2026-09-24 20:03:22'),
('23', '11', 'Basic Electronic', 'EET001', '2026-09-24 20:04:25'),
('24', '11', 'Introduction Electronic', 'EET002', '2026-09-24 20:09:50'),
('25', '11', 'Introduction Power', 'EET003', '2026-09-24 20:10:39'),
('26', '13', 'Introduction to Web', 'ICT001', '2026-09-24 20:11:37'),
('27', '13', 'Networking', 'ICT002', '2026-09-24 20:12:02'),
('28', '13', 'Introduction to Multimedia', 'ICT003', '2026-09-24 20:12:46'),
('29', '12', 'Chemistry', 'FDT001', '2026-09-25 20:38:10'),
('30', '12', 'Food Science', 'FDT002', '2026-09-25 20:40:22'),
('31', '14', 'Chemistry', 'BPT001', '2026-09-25 20:42:08'),
('32', '15', 'Subject 01', 'A001', '2026-09-26 00:03:08'),
('33', '15', 'Subject 02', 'A002', '2026-09-26 00:03:54'),
('34', '16', 'Subject 01', 'A201', '2026-09-26 00:04:50'),
('35', '18', 'Subject 01', 'A003', '2026-09-26 00:05:46'),
('36', '19', 'Subject 01', 'AP001', '2026-09-26 00:06:43'),
('37', '20', 'Subject 01', 'AP201', '2026-09-26 00:07:41'),
('38', '24', 'Subject 01', 'MN001', '2026-09-26 00:08:13'),
('39', '25', 'Subject 01', 'MN201', '2026-09-26 00:08:50');

-- Data for `notes`
INSERT INTO `notes` (`id`, `subject_id`, `title`, `description`, `pdf_path`, `thumbnail_path`, `uploaded_by`, `created_at`) VALUES
('1', '23', 'Lesson 01', '', 'uploads/pdfs/note_6ab5373253f5f.pdf', 'uploads/thumbnails/thumb_note_6ab5373253f5f.pdf.jpeg', '1', '2026-09-24 20:14:02'),
('2', '24', 'Lesson 01', '', 'uploads/pdfs/note_6ab53768a1670.pdf', 'uploads/thumbnails/thumb_note_6ab53768a1670.pdf.jpeg', '1', '2026-09-24 20:14:56'),
('3', '21', 'Lesson 01', '', 'uploads/pdfs/note_6ab537b7a3eb0.pdf', 'uploads/thumbnails/thumb_note_6ab537b7a3eb0.pdf.jpeg', '1', '2026-09-24 20:16:15'),
('4', '20', 'Lesson 01', '', 'uploads/pdfs/note_6ab537d3c4d81.pdf', 'uploads/thumbnails/thumb_note_6ab537d3c4d81.pdf.jpeg', '1', '2026-09-24 20:16:43'),
('5', '28', 'Lesson 01', '', 'uploads/pdfs/note_6ab53939ed2f5.pdf', 'uploads/thumbnails/thumb_note_6ab53939ed2f5.pdf.jpeg', '1', '2026-09-24 20:22:41'),
('6', '26', 'Lesson 01', '', 'uploads/pdfs/note_6ab539544a6cc.pdf', 'uploads/thumbnails/thumb_note_6ab539544a6cc.pdf.jpeg', '1', '2026-09-24 20:23:08'),
('7', '27', 'Lesson 01', '', 'uploads/pdfs/note_6ab53973dedb0.pdf', 'uploads/thumbnails/thumb_note_6ab53973dedb0.pdf.jpeg', '1', '2026-09-24 20:23:39'),
('8', '31', 'Lesson 1', '', 'uploads/pdfs/note_6ab6bdb428210.pdf', 'uploads/thumbnails/thumb_note_6ab6bdb428210.pdf.jpeg', '1', '2026-09-26 00:00:12'),
('9', '29', 'Lesson 1', '', 'uploads/pdfs/note_6ab6bdf5aac43.pdf', 'uploads/thumbnails/thumb_note_6ab6bdf5aac43.pdf.jpeg', '1', '2026-09-26 00:01:17'),
('10', '30', 'Lesson 1', '', 'uploads/pdfs/note_6ab6be2874b0b.pdf', 'uploads/thumbnails/thumb_note_6ab6be2874b0b.pdf.jpeg', '1', '2026-09-26 00:02:08'),
('11', '32', 'Lesson 01', '', 'uploads/pdfs/note_6ab6c056d0355.pdf', 'uploads/thumbnails/thumb_note_6ab6c056d0355.pdf.jpeg', '1', '2026-09-26 00:11:26'),
('12', '33', 'Lesson 01', '', 'uploads/pdfs/note_6ab6c0710800d.pdf', 'uploads/thumbnails/thumb_note_6ab6c0710800d.pdf.jpeg', '1', '2026-09-26 00:11:53'),
('13', '34', 'Lesson 01', '', 'uploads/pdfs/note_6ab6c0942cfb7.pdf', 'uploads/thumbnails/thumb_note_6ab6c0942cfb7.pdf.jpeg', '1', '2026-09-26 00:12:28'),
('14', '35', 'Lesson 01', '', 'uploads/pdfs/note_6ab6c0b83866f.pdf', 'uploads/thumbnails/thumb_note_6ab6c0b83866f.pdf.jpeg', '1', '2026-09-26 00:13:04'),
('15', '36', 'Lesson 01', '', 'uploads/pdfs/note_6ab6c0e7c6d7d.pdf', 'uploads/thumbnails/thumb_note_6ab6c0e7c6d7d.pdf.jpeg', '1', '2026-09-26 00:13:51'),
('16', '37', 'Lesson 01', '', 'uploads/pdfs/note_6ab6c111d6587.pdf', 'uploads/thumbnails/thumb_note_6ab6c111d6587.pdf.jpeg', '1', '2026-09-26 00:14:33'),
('17', '38', 'Lesson 01', '', 'uploads/pdfs/note_6ab6c13826c6c.pdf', 'uploads/thumbnails/thumb_note_6ab6c13826c6c.pdf.jpeg', '1', '2026-09-26 00:15:12'),
('18', '39', 'Lesson 01', '', 'uploads/pdfs/note_6ab6c15863bd6.pdf', 'uploads/thumbnails/thumb_note_6ab6c15863bd6.pdf.jpeg', '1', '2026-09-26 00:15:44');

-- Data for `note_tags`
INSERT INTO `note_tags` (`id`, `note_id`, `tag`) VALUES
('1', '1', 'eet001'),
('2', '8', 'bpt001'),
('3', '10', 'fdt002'),
('4', '11', 'a001'),
('5', '13', 'a201'),
('6', '15', 'ap001'),
('7', '16', 'ap201'),
('8', '17', 'mn001'),
('9', '18', 'mn201');

-- Data for `comments`
INSERT INTO `comments` (`id`, `note_id`, `user_id`, `parent_id`, `content`, `created_at`) VALUES
('1', '4', '1', NULL, 'Thank you', '2026-09-24 20:18:01'),
('2', '1', '1', NULL, 'woow', '2026-09-25 19:33:27');

-- Data for `comment_votes`
INSERT INTO `comment_votes` (`id`, `comment_id`, `user_id`, `vote_type`, `created_at`) VALUES
('1', '1', '1', '1', '2026-09-24 20:18:17');

SET FOREIGN_KEY_CHECKS = 1;
