// LIVE SEARCH FILTER
document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("searchInput");
    const table = document.querySelector("table");
    const rows = table.getElementsByTagName("tr");

    if (searchInput) {
        searchInput.addEventListener("keyup", function () {
            const filter = searchInput.value.toLowerCase();

            for (let i = 1; i < rows.length; i++) { 
                const cells = rows[i].getElementsByTagName("td");
                let match = false;

                for (let j = 0; j < cells.length; j++) {
                    if (cells[j] && cells[j].innerText.toLowerCase().includes(filter)) {
                        match = true;
                        break;
                    }
                }
                rows[i].style.display = match ? "" : "none";
            }
        });
    }

// DELETE CONFIRMATION
document.querySelectorAll("a.delete-btn").forEach(button => {
    button.addEventListener("click", function (e) {
        if (!confirm("Are you sure you want to delete this book?")) {
            e.preventDefault();
        }
    });
});


// BASIC FORM VALIDATION
    document.querySelectorAll("form").forEach(form => {
        form.addEventListener("submit", function (e) {
            const inputs = form.querySelectorAll("input[required], textarea[required]");
            let valid = true;

            inputs.forEach(input => {
                if (!input.value.trim()) {
                    input.style.border = "1px solid red";
                    valid = false;
                } else {
                    input.style.border = "1px solid #ccc";
                }
            });

            if (!valid) {
                e.preventDefault();
                alert("Please fill in all required fields.");
            }
        });
    });
});