<?php
include 'db_connect.php';

// Check if book ID is provided
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $query = "SELECT * FROM books WHERE id = $id";
    $result = $conn->query($query);

    if ($result->num_rows == 1) {
        $book = $result->fetch_assoc();
    } else {
        die("Book not found.");
    }
} else {
    die("No book ID provided.");
}

// Update book details
if (isset($_POST['update'])) {
    $title  = $conn->real_escape_string($_POST['title']);
    $author = $conn->real_escape_string($_POST['author']);
    $year   = intval($_POST['year']);
    $isbn   = $conn->real_escape_string($_POST['isbn']);

    $updateQuery = "UPDATE books 
                    SET title='$title', author='$author', year=$year, isbn='$isbn' 
                    WHERE id=$id";

    if ($conn->query($updateQuery)) {
        header("Location: view_catalog.php?msg=Book updated successfully");
        exit();
    } else {
        echo "Error updating book: " . $conn->error;
    }
}

// Remove book
if (isset($_POST['delete'])) {
    $deleteQuery = "DELETE FROM books WHERE id = $id";

    if ($conn->query($deleteQuery)) {
        header("Location: view_catalog.php?msg=Book deleted successfully");
        exit();
    } else {
        echo "Error deleting book: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit or Remove Book</title>
</head>
<body>
    <h2>Edit Book</h2>
    <form method="POST">
        <label>Title:</label><br>
        <input type="text" name="title" value="<?php echo $book['title']; ?>" required><br><br>

        <label>Author:</label><br>
        <input type="text" name="author" value="<?php echo $book['author']; ?>" required><br><br>

        <label>Year:</label><br>
        <input type="number" name="year" value="<?php echo $book['year']; ?>" required><br><br>

        <label>ISBN:</label><br>
        <input type="text" name="isbn" value="<?php echo $book['isbn']; ?>" required><br><br>

        <button type="submit" name="update">Update Book</button>
        <button type="submit" name="delete" onclick="return confirm('Are you sure you want to delete this book?')">Delete Book</button>
    </form>
</body>
</html>
