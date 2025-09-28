<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db_connect.php';
?>

<table border="1" cellpadding="8" cellspacing="0" width="100%">
    <tr>
        <th>Title</th>
        <th>Author</th>
        <th>Year</th>
        <th>ISBN</th>
        <th>Copies</th>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'librarian'): ?>
            <th>Actions</th>
        <?php endif; ?>
    </tr>
    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['title']); ?></td>
                <td><?php echo htmlspecialchars($row['author']); ?></td>
                <td><?php echo $row['year']; ?></td>
                <td><?php echo $row['isbn'] ?></td>
                <td><?php echo $row['copies']; ?></td>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'librarian'): ?>
                    <td>
                        <a href="edit_book.php?id=<?php echo $row['id']; ?>">✏️ Edit</a> |
                        <a href="delete_book.php?id=<?php echo $row['id']; ?>"
                            onclick="return confirm('Are you sure you want to delete this book?');">🗑️ Delete</a>
                    </td>
                <?php endif; ?>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="<?php echo (isset($_SESSION['role']) && $_SESSION['role'] === 'librarian') ? 6 : 5; ?>">
                No books found
            </td>
        </tr>
    <?php endif; ?>
</table>