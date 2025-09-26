-- Reset schema
DROP TABLE IF EXISTS borrow_records;
DROP TABLE IF EXISTS books;
DROP TABLE IF EXISTS users;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    role ENUM('librarian', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Books table
CREATE TABLE books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    year INT,  -- must be INT, not YEAR
    isbn VARCHAR(50) UNIQUE,
    genre VARCHAR(100),
    copies INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Borrow Records
CREATE TABLE borrow_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    borrow_date DATE DEFAULT (CURRENT_DATE),
    due_date DATE,
    return_date DATE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
);

-- Insert sample users
INSERT IGNORE INTO users (name, email, role) VALUES
('Librarian', 'librarian@library.com', 'librarian'),
('User', 'user@example.com', 'user');

-- Insert sample books
INSERT IGNORE INTO books (title, author, year, isbn, genre, copies) VALUES
('The Great Gatsby', 'F. Scott Fitzgerald', 1925, '9780743273565', 'Novel', 5),
('To Kill a Mockingbird', 'Harper Lee', 1960, '9780060935467', 'Fiction', 3),
('1984', 'George Orwell', 1949, '9780451524935', 'Dystopian', 4),
('Moby Dick', 'Herman Melville', 1851, '9781503280786', 'Adventure', 2);
