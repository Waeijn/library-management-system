<?php
include 'db_connect.php';  // ✅ use correct db file

$search = $_GET['search'] ?? '';
$searchEscaped = $conn->real_escape_string($search);

$where = $search
    ? "WHERE b.title LIKE '%$searchEscaped%' 
        OR b.author LIKE '%$searchEscaped%' 
        OR b.genre LIKE '%$searchEscaped%' 
        OR b.isbn LIKE '%$searchEscaped%'"
    : '';

$sql = "
    SELECT b.*, (b.copies - COUNT(br.id)) AS available_copies
    FROM books b
    LEFT JOIN borrow_records br ON b.id = br.book_id AND br.return_date IS NULL
    $where
    GROUP BY b.id
    ORDER BY b.title
";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Library Catalog</title>

    <!-- Inline CSS -->
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 40px;
            background-color: #f9f9f9;
        }

        h1 {
            color: #333;
            margin-bottom: 20px;
        }

        form {
            margin-bottom: 20px;
        }

        input[type="text"] {
            padding: 8px;
            width: 350px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            padding: 8px 12px;
            background-color: #007BFF;
            border: none;
            color: white;
            cursor: pointer;
            border-radius: 4px;
        }

        button:hover {
            background-color: #0056b3;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
        }

        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>📚 Library Catalog</h1>

    <form method="GET">
        <input type="text" name="search" placeholder="Search..." value="<?= htmlspecialchars($search) ?>" />
        <button>Search</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>Title</th><th>Author</th><th>Year</th><th>Genre</th><th>ISBN</th><th>Copies</th><th>Available</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows): ?>
                <?php while ($b = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($b['title']) ?></td>
                        <td><?= htmlspecialchars($b['author']) ?></td>
                        <td><?= $b['year'] ?></td>
                        <td><?= htmlspecialchars($b['genre']) ?></td>
                        <td><?= htmlspecialchars($b['isbn']) ?></td>
                        <td><?= $b['copies'] ?></td>
                        <td><?= max(0, $b['available_copies']) ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="7">No books found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Inline JS -->
    <script>
        // Autofocus the search input when the page loads
        window.onload = () => {
            const searchBox = document.querySelector('input[name="search"]');
            if (searchBox) searchBox.focus();
        };

        // Show a loading message when the form is submitted
        const form = document.querySelector('form');
        form.addEventListener('submit', function (event) {
            const searchBox = this.querySelector('input[name="search"]');
            if (!searchBox.value.trim()) {
                alert("Please enter a search term.");
                event.preventDefault();
            } else {
                // Show loading message while the page reloads
                const table = document.querySelector('table tbody');
                table.innerHTML = "<tr><td colspan='7'>Searching...</td></tr>";
            }
        });
    </script>
</body>
</html>
