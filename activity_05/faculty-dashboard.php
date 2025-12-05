<?php
require 'auth_check.php';

if ($_SESSION['role'] != 'faculty') {
    header("Location: " . $_SESSION['role'] . "-dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Faculty Dashboard - Attendance System</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <header>
    <h1>Faculty Dashboard</h1>
    <nav>
      <ul>
        <li><a href="#">Course Management</a></li>
        <li><a href="#">Session Overview</a></li>
        <li><a href="#">Attendance Reports</a></li>
        <li><a href="#" id="logoutBtn">Logout</a></li>
      </ul>
    </nav>
  </header>

  <main class="dashboard">
    <section>
      <h2>Course Management</h2>
      <button onclick="showCreateCourse()">Create New Course</button>
      <button onclick="loadMyCourses()">Refresh Courses</button>
      <div id="createCourseModal" style="display:none;">
        <input type="text" id="courseName" placeholder="Course Name">
        <input type="text" id="courseCode" placeholder="Course Code">
        <button onclick="createCourse()">Create</button>
        <button onclick="hideCreateCourse()">Cancel</button>
      </div>
      <div id="myCourses">
      </div>
    </section>

    <section>
      <h2>Student Requests</h2>
      <button onclick="loadPendingRequests()">Refresh Requests</button>
      <div id="pendingRequests">
      </div>
    </section>

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
</body>
</html>