<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'librarian') {
    header("Location: index.php");
    exit;
}

include 'db_connect.php';

$message = "";

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Book ID is missing.");
}

$book_id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT * FROM books WHERE id = ?");
$stmt->bind_param("i", $book_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Book not found.");
}

$book = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $year = !empty($_POST['year']) ? intval($_POST['year']) : null;
    $isbn = trim($_POST['isbn']);
    $genre = trim($_POST['genre']);
    $copies = intval($_POST['copies']);

    if (!empty($title) && !empty($author) && $copies > 0) {
        $stmt = $conn->prepare("
            UPDATE books 
            SET title = ?, author = ?, year = ?, isbn = ?, genre = ?, copies = ? 
            WHERE id = ?
        ");
        $stmt->bind_param("sssssii", $title, $author, $year, $isbn, $genre, $copies, $book_id);

        if ($stmt->execute()) {
            $message = "Book updated successfully!";
            // Refresh data
            header("Location: edit_book.php?id=$book_id&updated=1");
            exit;
        } else {
            $message = "Error updating book: " . $stmt->error;
        }
    } else {
        $message = "Title, Author, and Copies are required!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Book</title>
    <link rel="stylesheet" href="assets/style.css">
    <script src="assets/main.js"></script>
</head>

<body>
    <h1>Edit Book</h1>

    <?php if (isset($_GET['updated'])): ?>
        <p><strong>Book updated successfully!</strong></p>
    <?php elseif ($message): ?>
        <p><strong><?php echo $message; ?></strong></p>
    <?php endif; ?>

    <form method="POST">
        <label>Title:</label><br>
        <input type="text" name="title" value="<?php echo htmlspecialchars($book['title']); ?>" required><br><br>

        <label>Author:</label><br>
        <input type="text" name="author" value="<?php echo htmlspecialchars($book['author']); ?>" required><br><br>

        <label>Year:</label><br>
        <input type="number" name="year" min="0" max="9999"
            value="<?php echo htmlspecialchars($book['year']); ?>"><br><br>

        <label>ISBN:</label><br>
        <input type="text" name="isbn" value="<?php echo htmlspecialchars($book['isbn']); ?>"><br><br>

        <label>Genre:</label><br>
        <input type="text" name="genre" value="<?php echo htmlspecialchars($book['genre']); ?>"><br><br>

        <label>Copies:</label><br>
        <input type="number" name="copies" min="1" value="<?php echo htmlspecialchars($book['copies']); ?>"
            required><br><br>

        <button type="submit">Save Changes</button>
    </form>

    <p><a href="index.php">Back to Home</a></p>
</body>

</html>