<?php
require 'auth_check.php';

if ($_SESSION['role'] != 'intern') {
    header("Location: " . $_SESSION['role'] . "-dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Intern Dashboard - Attendance System</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <header>
    <h1>Faculty Intern Dashboard</h1>
    <nav>
      <ul>
        <li><a href="#">My Tasks</a></li>
        <li><a href="#">Sessions</a></li>
        <li><a href="#" id="logoutBtn">Logout</a></li>
      </ul>
    </nav>
  </header>

  <main class="dashboard">
    <section>
      <h2>Welcome</h2>
      <p>Intern dashboard functionality coming soon...</p>
    </section>
  </main>

  <footer>
    <p>&copy; 2025 Attendance System | Ashesi University</p>
  </footer>
  
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    document.getElementById('logoutBtn').addEventListener('click', function(e) {
      e.preventDefault();
      fetch('logout.php')
        .then(res => res.json())
        .then(data => {
          if (data.logout) {
            Swal.fire('Logged Out', 'You have been logged out', 'success')
              .then(() => window.location.href = 'login.html');
          }
        });
    });
  </script>
</body>
</html>
