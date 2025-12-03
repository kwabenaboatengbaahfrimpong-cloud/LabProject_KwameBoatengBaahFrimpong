<?php
require 'auth_check.php';

if ($_SESSION['role'] != 'student') {
    header("Location: " . $_SESSION['role'] . "-dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Dashboard - Attendance System</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <header>
    <h1>Student Dashboard</h1>
    <nav>
      <ul>
        <li><a href="#">My Courses</a></li>
        <li><a href="#">Session Schedule</a></li>
        <li><a href="#">Grades & Reports</a></li>
        <li><a href="#" id="logoutBtn">Logout</a></li>
      </ul>
    </nav>
  </header>

  <main class="dashboard">
    <section>
      <h2>My Courses</h2>
      <button onclick="loadMyCourses()">Refresh Courses</button>
      <div id="myCourses">
      </div>
    </section>

    <section>
      <h2>Join Course</h2>
      <button onclick="showJoinCourse()">Search for Courses</button>
      <div id="joinCourseModal" style="display:none;">
        <input type="text" id="searchCourse" placeholder="Search by course name or code">
        <button onclick="searchCourses()">Search</button>
        <div id="searchResults"></div>
      </div>
    </section>

    <section>
      <h2>Session Schedule</h2>
      <p>Here's your session timetable for the week.</p>
    </section>
  </main>

  <footer>
    <p>&copy; 2025 Attendance System | Ashesi University</p>
  </footer>
  
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="student.js"></script>
</body>
</html>
