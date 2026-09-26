# StudyMate - University Notes Platform

A public academic resource web application designed for university students to access study materials, lecture notes, and academic documents organized by category, department, and subject.

---

## Technology Stack

- **Server Environment:** WAMP Server / XAMPP (Apache, MySQL, PHP 8.x)
- **Backend:** PHP (Native, Object-Oriented MySQLi with Prepared Statements)
- **Database:** MySQL
- **Frontend:** Semantic HTML5, Vanilla CSS3 (Custom Responsive Design), Vanilla JavaScript (ES6+)
- **Icons & Typography:** Bootstrap Icons, Google Fonts (Inter)

---

## Features Overview

### Public & Student Features
- **Browse by Category & Department:** Navigate through organized academic disciplines.
- **Search & Sort Notes:** Live search and sort notes by newest, oldest, title (A-Z), and popularity.
- **Direct PDF Download:** Clicking on any note thumbnail or the download button immediately downloads the PDF document.
- **Save Note on Thumbnail Card:** Directly save or bookmark notes using the bookmark button located on the thumbnail card.
- **Dedicated "Save Note" Page:** View all saved study materials in one dedicated page with real-time keyword search, category filtering, and custom sorting.
- **Discussion & Comments:** Community comments and nested replies on note pages with like/dislike voting.
- **Contact Inquiries:** Contact form with input validation and message storage for administrators.

### Administrator Features
- **Admin Dashboard:** Overview of categories, departments, subjects, notes, and messages.
- **Category & Department Management:** Full CRUD operations with custom icon selection.
- **Subject Management:** Create and organize subjects mapped to departments.
- **Notes Management:** Upload lecture notes (PDFs) with automated thumbnail previews and tagging.
- **Messages Center:** Review and manage student inquiries sent via the contact form.

---

## Setup & Installation Guide

### Prerequisites
- **WAMP Server** (Recommended) or **XAMPP** installed on your system.
- Web browser (Chrome, Edge, Firefox, or Safari).

---

### Method A: Running with WAMP Server

1. **Place Project in WAMP `www` Directory:**
   - Copy or clone this repository into your WAMP `www` folder:
     ```text
     C:\wamp64\www\studymate\
     ```

2. **Start WAMP Services:**
   - Launch WAMP Server from the Start menu or desktop shortcut.
   - Ensure the WAMP icon in the Windows notification area turns **Green** (indicating Apache and MySQL services are active).

3. **Import Database via phpMyAdmin:**
   - Open your browser and navigate to: `http://localhost/phpmyadmin/`
   - Log in (Default username: `root`, password: leave blank or your configured MySQL password).
   - Click on the **Import** tab at the top.
   - Click **Choose File** and select `database.sql` from `C:\wamp64\www\studymate\database.sql`.
   - Click **Import** (or **Go**) at the bottom.
   - The `note_platform` database and all sample tables/data will be created automatically.

4. **Verify Database Configuration:**
   - Open `includes/db.php` in a code editor.
   - Ensure database credentials match your WAMP settings:
     - `DB_HOST`: `localhost`
     - `DB_USER`: `root`
     - `DB_PASS`: `""` (empty by default in WAMP)
     - `DB_NAME`: `note_platform`

5. **Access the Application:**
   - Open your browser and navigate to:
     ```text
     http://localhost/studymate/
     ```

---

### Method B: Running with XAMPP

1. **Place Project in XAMPP `htdocs` Directory:**
   - Copy or clone the project folder into `C:\xampp\htdocs\studymate\`

2. **Start Apache & MySQL:**
   - Open the **XAMPP Control Panel**.
   - Click **Start** next to **Apache** and **MySQL**.

3. **Import the Database:**
   - Navigate to `http://localhost/phpmyadmin/`
   - Click **Import**, select `database.sql`, and execute.

4. **Access the Application:**
   - Navigate to `http://localhost/studymate/`

---

## Default Login Credentials

### Administrator Account
- **Email:** `admin@studymate.com`
- **Password:** `admin123`
- **Access:** Admin panel link appears in profile menu and at `/studymate/admin/index.php`.

### Regular Student Account
- Create a new account via the **Register** button (`/studymate/auth/register.php`), or log in using any registered credentials.

---

## Project Directory Structure

```text
studymate/
├── css/
│   └── style.css          # Main responsive stylesheet
├── js/
│   └── script.js           # Client-side validation, search, and interactions
├── images/                 # Static graphical assets
├── uploads/
│   ├── pdfs/               # Uploaded lecture note PDF documents
│   ├── thumbnails/         # Note thumbnail preview images
│   └── profiles/           # User profile avatar uploads
├── includes/
│   ├── db.php              # Database connection and environment configuration
│   ├── functions.php       # Helper functions (authentication, CSRF, formatting)
│   ├── navbar.php          # Main navigation bar (Home, All Notes, Save Note, Contact)
│   ├── footer.php          # Site footer component
│   └── admin_nav.php       # Admin sidebar navigation
├── auth/
│   ├── register.php        # User registration page
│   ├── login.php           # User login page
│   └── logout.php          # Session termination
├── actions/
│   ├── save_note.php       # Save/bookmark note endpoint
│   ├── unsave_note.php     # Remove saved note endpoint
│   ├── add_comment.php     # Post comment or reply endpoint
│   └── vote_comment.php    # Upvote/downvote comment endpoint
├── admin/
│   ├── index.php           # Admin dashboard and metric counters
│   ├── categories.php      # Manage academic categories
│   ├── departments.php     # Manage university departments
│   ├── subjects.php        # Manage subjects and course codes
│   ├── notes.php           # Upload and manage lecture notes
│   └── messages.php        # Manage student inquiries
├── index.php               # Landing page with category browser
├── departments.php         # Department listing by category
├── subjects.php            # All notes with search, sort, and thumbnail actions
├── saved_notes.php         # Dedicated Save Note page with search & filter
├── save_notes.php          # Save Note route alias
├── view_note.php           # Note details, direct download, and comments
├── contact.php             # Contact and message submission page
├── account.php             # User profile and password settings
├── database.sql            # Clean MySQL database schema & seed data
└── README.md               # Project documentation and setup guide
```

---

## Database Schema Summary

- `categories` - Academic categories (e.g., Computing & IT, Engineering, Sciences).
- `departments` - Departments mapped to categories.
- `subjects` - Subjects with course codes mapped to departments.
- `users` - User accounts with role-based access (`admin` and `user`).
- `notes` - Study material metadata, PDF file paths, and thumbnail references.
- `saved_notes` - Student bookmarked notes mapped by `user_id` and `note_id`.
- `comments` - Threaded discussion comments and replies.
- `comment_votes` - Like/dislike score records per user and comment.
- `note_tags` - Topic keywords for enhanced searchability.
- `messages` - Contact form inquiries for administrative follow-up.
