<?php
include 'db_connect.php'; // DB connection

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $year = !empty($_POST['year']) ? intval($_POST['year']) : null;
    $isbn = trim($_POST['isbn']);
    $genre = trim($_POST['genre']);
    $copies = intval($_POST['copies']);

    if (!empty($title) && !empty($author) && $copies > 0) {
        if ($year === null) {
            $stmt = $conn->prepare("INSERT INTO books (title, author, year, isbn, genre, copies) VALUES (?, ?, NULL, ?, ?, ?)");
            $stmt->bind_param("sssi", $title, $author, $isbn, $genre, $copies);
        } else {
            $stmt = $conn->prepare("INSERT INTO books (title, author, year, isbn, genre, copies) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssissi", $title, $author, $year, $isbn, $genre, $copies);
        }

        if ($stmt->execute()) {
            $message = "✅ Book added successfully!";
        } else {
            $message = "❌ Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $message = "⚠️ Title, Author, and Copies are required!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Add New Book</title>
</head>

<body>
    <h1>Add a New Book</h1>

    <?php if ($message): ?>
        <p><strong><?php echo $message; ?></strong></p>
    <?php endif; ?>

    <form method="POST" action="add_book.php">
        <label>Title:</label><br>
        <input type="text" name="title" required><br><br>

        <label>Author:</label><br>
        <input type="text" name="author" required><br><br>

        <label>Year:</label><br>
        <input type="number" name="year" min="1000" max="9999"><br><br>

        <label>ISBN:</label><br>
        <input type="text" name="isbn"><br><br>

        <label>Genre:</label><br>
        <input type="text" name="genre"><br><br>

        <label>Copies:</label><br>
        <input type="number" name="copies" min="1" value="1" required><br><br>

        <button type="submit">Add Book</button>
    </form>

    <p><a href="index.php">⬅ Back to Home</a></p>
</body>

</html>