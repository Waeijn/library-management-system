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

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Library Management System</title>
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
        <a href="borrow_return.php">Borrow/Return</a>
        <a href="logout.php">Logout</a>
    </nav>

    <h2>Available Books</h2>

    <!--Search Form -->
    <form action="index.php" method="GET" style="margin-bottom: 20px">
        <input type="search" name="search" placeholder="Search Book">
        <button type="submit">Search</button>
    </form>
    <?php include 'view_catalog.php'; ?>
</body>

</html>