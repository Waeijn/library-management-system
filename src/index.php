<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
include 'db_connect.php';

//Search Book
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if (!empty($search)) {

    $stmt = $conn->prepare("SELECT * FROM books 
    WHERE title LIKE ? OR author LIKE ? OR isbn LIKE ?");

    $like = "%" . $search . "%";
    $stmt->bind_param("sss", $like, $like, $like);
    $stmt->execute();
    $result = $stmt->get_result();

} else {
    $result = $conn->query("SELECT * FROM books");
}

//Delete Book
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];

    $stmt = $conn->prepare("DELETE FROM books WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();

    header("Location: index.php");
}

// Fetch borrow records
if ($_SESSION['role'] === 'librarian') {
    // Librarian sees ALL borrow records
    $borrowQuery = "
        SELECT br.id, u.name AS user_name, b.title, br.borrow_date, br.due_date, br.return_date
        FROM borrow_records br
        JOIN users u ON br.user_id = u.id
        JOIN books b ON br.book_id = b.id
        ORDER BY br.borrow_date DESC
    ";
} else {
    // Normal user only sees their own records
    $user_id = $_SESSION['user_id'];
    $borrowQuery = "
        SELECT br.id, b.title, br.borrow_date, br.due_date, br.return_date
        FROM borrow_records br
        JOIN books b ON br.book_id = b.id
        WHERE br.user_id = $user_id
        ORDER BY br.borrow_date DESC
    ";
}

$borrowRecords = $conn->query($borrowQuery);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Library Management System</title>
    <link rel="stylesheet" href="assets/style.css">
    <script src="assets/main.js"></script>
</head>

<body>
    <h1>Welcome to the Library</h1>
    <p>
        Logged in as:
        <strong>
            <?php echo htmlspecialchars($_SESSION['name']); ?>
            (<?php echo ucfirst($_SESSION['role']); ?>)
        </strong>
    </p>

    <nav>
        <?php if ($_SESSION['role'] === 'librarian'): ?>
            <a href="add_book.php">Add Book</a>
        <?php endif; ?>

        <?php if ($_SESSION['role'] === 'librarian'): ?>
            <a href="borrow_return.php">Return Books</a>
        <?php else: ?>
            <a href="borrow_return.php">Borrow Books</a>
        <?php endif; ?>

        <a href="logout.php">Logout</a>
    </nav>


    <h2>Book List</h2>

    <!--Search Form -->
    <input type="text" id="searchInput" placeholder="Search books..."
        style="margin-bottom: 20px; padding: 5px; width: 250px;">

    <?php include 'view_catalog.php'; ?>

    <hr>
    <h2>Borrow Records</h2>

    <?php if ($borrowRecords && $borrowRecords->num_rows > 0): ?>
        <table>
            <tr>
                <?php if ($_SESSION['role'] === 'librarian'): ?>
                    <th>User</th>
                <?php endif; ?>
                <th>Book Title</th>
                <th>Borrow Date</th>
                <th>Due Date</th>
                <th>Return Date</th>
            </tr>

            <?php while ($row = $borrowRecords->fetch_assoc()): ?>
                <tr>
                    <?php if ($_SESSION['role'] === 'librarian'): ?>
                        <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                    <?php endif; ?>
                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                    <td><?php echo $row['borrow_date']; ?></td>
                    <td><?php echo $row['due_date']; ?></td>
                    <td><?php echo $row['return_date'] ?: 'Not Returned'; ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No borrow records found.</p>
    <?php endif; ?>

    <br>

</body>

</html>