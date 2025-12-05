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
      <h2>Attendance Management</h2>
      <div id="attendanceSection">
        <p>Select a course to manage attendance:</p>
        <select id="courseSelect" onchange="loadCourseSessions()">
          <option value="">-- Select Course --</option>
        </select>
        <div id="sessionManagement" style="display:none;">
          <h3>Create New Session</h3>
          <input type="date" id="sessionDate">
          <button onclick="createSession()">Create Session</button>
          
          <h3>Existing Sessions</h3>
          <div id="sessionList"></div>
          
          <h3>Attendance Report</h3>
          <button onclick="generateReport()">Generate Report</button>
          <div id="attendanceReport"></div>
        </div>
      </div>
    </section>
  </main>

  <footer>
    <p>&copy; 2025 Attendance System | Ashesi University</p>
  </footer>
  
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="faculty.js"></script>
  <script>
    // Load courses when page loads
    window.addEventListener('load', function() {
      loadMyCourses(); // This function is in faculty.js and works for interns too
    });
    
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