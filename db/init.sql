-- Create users table (for librarians & readers)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    role ENUM('librarian', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create books table
CREATE TABLE IF NOT EXISTS books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    year YEAR,
    isbn VARCHAR(50) UNIQUE,
    genre VARCHAR(100),
    copies INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create borrow_records table
CREATE TABLE IF NOT EXISTS borrow_records (
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
INSERT INTO users (name, email, role) VALUES
('Admin Librarian', 'admin@library.com', 'librarian'),
('John Doe', 'john@example.com', 'user'),
('Jane Smith', 'jane@example.com', 'user');

-- Insert sample books
INSERT INTO books (title, author, year, isbn, genre, copies) VALUES
('The Great Gatsby', 'F. Scott Fitzgerald', 1925, '9780743273565', 'Novel', 5),
('To Kill a Mockingbird', 'Harper Lee', 1960, '9780060935467', 'Fiction', 3),
('1984', 'George Orwell', 1949, '9780451524935', 'Dystopian', 4),
('Moby Dick', 'Herman Melville', 1851, '9781503280786', 'Adventure', 2);
