<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include 'db_connect.php';

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$message = "";

/* Borrow a Book (ONLY FOR USERS) */
if ($role === 'user' && isset($_POST['borrow_book_id'])) {
    $book_id = intval($_POST['borrow_book_id']);
    $due_date = date('Y-m-d', strtotime('+7 days'));

    $check = $conn->prepare("SELECT copies FROM books WHERE id = ?");
    $check->bind_param("i", $book_id);
    $check->execute();
    $result = $check->get_result();
    $book = $result->fetch_assoc();

    if ($book && $book['copies'] > 0) {
        $stmt = $conn->prepare("INSERT INTO borrow_records (user_id, book_id, due_date) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $user_id, $book_id, $due_date);
        $stmt->execute();

        $update = $conn->prepare("UPDATE books SET copies = copies - 1 WHERE id = ?");
        $update->bind_param("i", $book_id);
        $update->execute();

        $message = "Book successfully borrowed!";
    } else {
        $message = "No copies available.";
    }
}

/*Return a Book (ONLY FOR LIBRARIANS) */
if ($role === 'librarian' && isset($_POST['return_record_id'])) {
    $record_id = intval($_POST['return_record_id']);

    $getBook = $conn->prepare("SELECT book_id, user_id FROM borrow_records WHERE id = ?");
    $getBook->bind_param("i", $record_id);
    $getBook->execute();
    $bookResult = $getBook->get_result();
    $bookData = $bookResult->fetch_assoc();

    if ($bookData) {
        $book_id = $bookData['book_id'];

        $stmt = $conn->prepare("UPDATE borrow_records SET return_date = CURRENT_DATE WHERE id = ?");
        $stmt->bind_param("i", $record_id);
        $stmt->execute();

        $update = $conn->prepare("UPDATE books SET copies = copies + 1 WHERE id = ?");
        $update->bind_param("i", $book_id);
        $update->execute();

        $message = "Book returned successfully!";
    } else {
        $message = "Invalid return request.";
    }
}

/* Fetch Available Books (users only) */
$availableBooks = null;
if ($role === 'user') {
    $availableBooks = $conn->query("SELECT * FROM books WHERE copies > 0");
}

/* Fetch All Borrowed Books (librarian returns for any user) */
if ($role === 'librarian') {
    $borrowedBooks = $conn->query("
        SELECT br.id AS record_id, b.title, b.author, u.name AS borrower, br.borrow_date, br.due_date
        FROM borrow_records br
        JOIN books b ON br.book_id = b.id
        JOIN users u ON br.user_id = u.id
        WHERE br.return_date IS NULL
    ");
} else {
    $borrowedBooks = $conn->prepare("
        SELECT br.id AS record_id, b.title, b.author, br.borrow_date, br.due_date 
        FROM borrow_records br
        JOIN books b ON br.book_id = b.id
        WHERE br.user_id = ? AND br.return_date IS NULL
    ");
    $borrowedBooks->bind_param("i", $user_id);
    $borrowedBooks->execute();
    $borrowedBooks = $borrowedBooks->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Borrow / Return Books</title>
    <link rel="stylesheet" href="assets/style.css">
    <script src="assets/main.js"></script>
</head>

<body>
    <h1>Borrow & Return Books</h1>
    <p>Logged in as: <strong><?php echo ucfirst($role); ?></strong></p>

    <?php if ($message): ?>
        <p><strong><?php echo $message; ?></strong></p>
    <?php endif; ?>

    <?php if ($role === 'user'): ?>
        <h2>Available Books</h2>
        <?php if ($availableBooks && $availableBooks->num_rows > 0): ?>
            <table border="1" cellpadding="5">
                <tr>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Copies</th>
                    <th>Action</th>
                </tr>
                <?php while ($book = $availableBooks->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($book['title']); ?></td>
                        <td><?php echo htmlspecialchars($book['author']); ?></td>
                        <td><?php echo $book['copies']; ?></td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="borrow_book_id" value="<?php echo $book['id']; ?>">
                                <button type="submit">Borrow</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>No books available to borrow.</p>
        <?php endif; ?>
    <?php endif; ?>

    <h2><?php echo ($role === 'librarian') ? "All Borrowed Books" : "Your Borrowed Books"; ?></h2>
    <?php if ($borrowedBooks->num_rows > 0): ?>
        <table border="1" cellpadding="5">
            <tr>
                <th>Title</th>
                <th>Author</th>
                <?php if ($role === 'librarian'): ?>
                    <th>Borrower</th>
                <?php endif; ?>
                <th>Borrowed</th>
                <th>Due Date</th>
                <?php if ($role === 'librarian'): ?>
                    <th>Action</th>
                <?php endif; ?>
            </tr>
            <?php while ($row = $borrowedBooks->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                    <td><?php echo htmlspecialchars($row['author']); ?></td>
                    <?php if ($role === 'librarian'): ?>
                        <td><?php echo htmlspecialchars($row['borrower']); ?></td>
                    <?php endif; ?>
                    <td><?php echo $row['borrow_date']; ?></td>
                    <td><?php echo $row['due_date']; ?></td>
                    <?php if ($role === 'librarian'): ?>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="return_record_id" value="<?php echo $row['record_id']; ?>">
                                <button type="submit">Return</button>
                            </form>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>
            <?php echo ($role === 'librarian') ? "No borrowed books." : "You haven't borrowed any books yet."; ?>
        </p>
    <?php endif; ?>

    <p><a href="index.php">⬅ Back to Home</a></p>
</body>

</html>