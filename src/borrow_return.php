<?php
require 'db_connect.php'; // database connection

$message = '';

// Handle Borrow
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'borrow') {
    $user_id = intval($_POST['user_id']);
    $book_id = intval($_POST['book_id']);

    if ($user_id > 0 && $book_id > 0) {
        $conn->begin_transaction();
        try {
            // Check if already borrowed
            $check = $conn->prepare("
                SELECT COUNT(*) FROM borrow_records 
                WHERE user_id = ? AND book_id = ? AND return_date IS NULL
            ");
            $check->bind_param("ii", $user_id, $book_id);
            $check->execute();
            $check->bind_result($active_borrows);
            $check->fetch();
            $check->close();

            if ($active_borrows > 0) {
                throw new Exception("This user already borrowed this book and has not returned it.");
            }

            // Lock book row
            $stmt = $conn->prepare("SELECT copies FROM books WHERE id = ? FOR UPDATE");
            $stmt->bind_param("i", $book_id);
            $stmt->execute();
            $stmt->bind_result($copies);
            $stmt->fetch();
            $stmt->close();

            if ($copies <= 0) {
                throw new Exception("No available copies of this book.");
            }

            // Update copies
            $upd = $conn->prepare("UPDATE books SET copies = copies - 1 WHERE id = ?");
            $upd->bind_param("i", $book_id);
            $upd->execute();
            $upd->close();

            // Insert borrow record
            $due_date = date('Y-m-d', strtotime('+14 days'));
            $ins = $conn->prepare("
                INSERT INTO borrow_records (user_id, book_id, borrow_date, due_date) 
                VALUES (?, ?, CURDATE(), ?)
            ");
            $ins->bind_param("iis", $user_id, $book_id, $due_date);
            $ins->execute();
            $ins->close();

            $conn->commit();
            $message = "Book borrowed successfully! Due date: $due_date";
        } catch (Exception $e) {
            $conn->rollback();
            $message = "Error: " . $e->getMessage();
        }
    }
}

// Handle Return
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'return') {
    if (isset($_POST['borrow_id']) && intval($_POST['borrow_id']) > 0) {
        $borrow_id = intval($_POST['borrow_id']);

        $conn->begin_transaction();
        try {
            $sel = $conn->prepare("SELECT book_id, return_date FROM borrow_records WHERE id = ? FOR UPDATE");
            $sel->bind_param("i", $borrow_id);
            $sel->execute();
            $sel->bind_result($book_id, $return_date);
            $sel->fetch();
            $sel->close();

            if (!$book_id) {
                throw new Exception("Borrow record not found.");
            }
            if ($return_date) {
                throw new Exception("This book has already been returned.");
            }

            // Update return date
            $upd = $conn->prepare("UPDATE borrow_records SET return_date = CURDATE() WHERE id = ?");
            $upd->bind_param("i", $borrow_id);
            $upd->execute();
            $upd->close();

            // Restore copies
            $upb = $conn->prepare("UPDATE books SET copies = copies + 1 WHERE id = ?");
            $upb->bind_param("i", $book_id);
            $upb->execute();
            $upb->close();

            $conn->commit();
            $message = "Book returned successfully!";
        } catch (Exception $e) {
            $conn->rollback();
            $message = "Error: " . $e->getMessage();
        }
    }
}

// Load users & books
$users = $conn->query("SELECT id, name FROM users ORDER BY name");
$books = $conn->query("SELECT id, title, copies FROM books ORDER BY title");

// Load borrow records
$records = $conn->query("
    SELECT br.id, u.name AS user_name, b.title AS book_title, 
           br.borrow_date, br.due_date, br.return_date
    FROM borrow_records br
    JOIN users u ON br.user_id = u.id
    JOIN books b ON br.book_id = b.id
    ORDER BY br.borrow_date DESC
");
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Borrow / Return Books</title>
</head>
<body>
    <h1>Borrow / Return Books</h1>

    <?php if ($message): ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <h2>Borrow a Book</h2>
    <form method="post">
        <label>User:
            <select name="user_id" required>
                <option value="">-- Select user --</option>
                <?php while ($u = $users->fetch_assoc()): ?>
                    <option value="<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['name']); ?></option>
                <?php endwhile; ?>
            </select>
        </label>
        <label>Book:
            <select name="book_id" required>
                <option value="">-- Select book --</option>
                <?php while ($b = $books->fetch_assoc()): ?>
                    <option value="<?php echo $b['id']; ?>">
                        <?php echo htmlspecialchars($b['title']) . " (copies: " . $b['copies'] . ")"; ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </label>
        <input type="hidden" name="action" value="borrow">
        <button type="submit">Borrow</button>
    </form>

    <h2>Borrow Records</h2>
    <?php if ($records->num_rows > 0): ?>
    <table border="1" cellpadding="5">
        <tr>
            <th>ID</th><th>User</th><th>Book</th>
            <th>Borrow Date</th><th>Due Date</th><th>Return Date</th><th>Status</th><th>Action</th>
        </tr>
        <?php while ($r = $records->fetch_assoc()): ?>
            <?php $overdue = (!$r['return_date'] && $r['due_date'] < date('Y-m-d')); ?>
            <tr>
                <td><?php echo $r['id']; ?></td>
                <td><?php echo htmlspecialchars($r['user_name']); ?></td>
                <td><?php echo htmlspecialchars($r['book_title']); ?></td>
                <td><?php echo $r['borrow_date']; ?></td>
                <td><?php echo $r['due_date']; ?></td>
                <td><?php echo $r['return_date'] ?: '-'; ?></td>
                <td>
                    <?php 
                        if ($r['return_date']) echo "Returned";
                        elseif ($overdue) echo "Overdue!";
                        else echo "Borrowed";
                    ?>
                </td>
                <td>
                    <?php if (!$r['return_date']): ?>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="borrow_id" value="<?php echo $r['id']; ?>">
                            <input type="hidden" name="action" value="return">
                            <button type="submit">Return</button>
                        </form>
                    <?php else: ?> ✔ <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
    <?php else: ?>
        <p>No borrow records found.</p>
    <?php endif; ?>
</body>
</html>
<?php $conn->close(); ?>
