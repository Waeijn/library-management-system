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


    <h2>Books</h2>

    <!--Search Form -->
    <input type="text" id="searchInput" placeholder="Search books..."
        style="margin-bottom: 20px; padding: 5px; width: 250px;">

    <?php include 'view_catalog.php'; ?>
</body>

</html>