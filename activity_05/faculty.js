window.onload = function() {
  loadMyCourses();
  loadPendingRequests();
  
  document.getElementById('logoutBtn').onclick = function(e) {
    e.preventDefault();
    logout();
  };
};

function showCreateCourse() {
  document.getElementById('createCourseModal').style.display = 'block';
}

function hideCreateCourse() {
  document.getElementById('createCourseModal').style.display = 'none';
}

function createCourse() {
  var name = document.getElementById('courseName').value;
  var code = document.getElementById('courseCode').value;
  
  if (!name || !code) {
    alert('Please fill in all fields');
    return;
  }
  
  var formData = new FormData();
  formData.append('action', 'create');
  formData.append('course_name', name);
  formData.append('course_code', code);
  
  fetch('courses.php', {
    method: 'POST',
    body: formData
  })
  .then(function(res) { return res.json(); })
  .then(function(data) {
    if (data.success) {
      alert(data.message);
      hideCreateCourse();
      loadMyCourses();
      document.getElementById('courseName').value = '';
      document.getElementById('courseCode').value = '';
    } else {
      alert(data.message);
    }
  });
}

function loadMyCourses() {
  var formData = new FormData();
  formData.append('action', 'my_courses');
  
  fetch('courses.php', {
    method: 'POST',
    body: formData
  })
  .then(function(res) { return res.json(); })
  .then(function(data) {
    if (data.success) {
      var div = document.getElementById('myCourses');
      var select = document.getElementById('courseSelect');
      var studentSelect = document.getElementById('studentCourseSelect');
      
      // Clear select options except the first one
      select.innerHTML = '<option value="">-- Select Course --</option>';
      if (studentSelect) {
        studentSelect.innerHTML = '<option value="">-- Select Course --</option>';
      }
      
      if (data.courses.length > 0) {
        var html = '<ul>';
        for (var i = 0; i < data.courses.length; i++) {
          html += '<li>' + data.courses[i].course_name + ' (' + data.courses[i].course_code + ')</li>';
          // Add to select dropdown
          var option = document.createElement('option');
          option.value = data.courses[i].id;
          option.text = data.courses[i].course_name + ' (' + data.courses[i].course_code + ')';
          select.appendChild(option);
          
          // Also add to student select if it exists
          if (studentSelect) {
            var studentOption = option.cloneNode(true);
            studentSelect.appendChild(studentOption);
          }
        }
        html += '</ul>';
        div.innerHTML = html;
      } else {
        div.innerHTML = '<p>No courses created yet</p>';
      }
    }
  });
}

function loadPendingRequests() {
  var formData = new FormData();
  formData.append('action', 'pending');
  
  fetch('enrollments.php', {
    method: 'POST',
    body: formData
  })
  .then(function(res) { return res.json(); })
  .then(function(data) {
    if (data.success) {
      var div = document.getElementById('pendingRequests');
      if (data.requests.length > 0) {
        var html = '<ul>';
        for (var i = 0; i < data.requests.length; i++) {
          var req = data.requests[i];
          html += '<li>' + req.fullname + ' wants to join ' + req.course_name;
          html += ' <button onclick="approveRequest(' + req.id + ')">Approve</button>';
          html += ' <button onclick="rejectRequest(' + req.id + ')">Reject</button></li>';
        }
        html += '</ul>';
        div.innerHTML = html;
      } else {
        div.innerHTML = '<p>No pending requests</p>';
      }
    }
  });
}

function approveRequest(enrollmentId) {
  var formData = new FormData();
  formData.append('action', 'approve');
  formData.append('enrollment_id', enrollmentId);
  
  fetch('enrollments.php', {
    method: 'POST',
    body: formData
  })
  .then(function(res) { return res.json(); })
  .then(function(data) {
    if (data.success) {
      alert(data.message);
      loadPendingRequests();
    } else {
      alert(data.message);
    }
  });
}

function rejectRequest(enrollmentId) {
  var formData = new FormData();
  formData.append('action', 'reject');
  formData.append('enrollment_id', enrollmentId);
  
  fetch('enrollments.php', {
    method: 'POST',
    body: formData
  })
  .then(function(res) { return res.json(); })
  .then(function(data) {
    if (data.success) {
      alert(data.message);
      loadPendingRequests();
    } else {
      alert(data.message);
    }
  });
}

// Attendance functions
function loadCourseSessions() {
  var courseId = document.getElementById('courseSelect').value;
  if (!courseId) {
    document.getElementById('sessionManagement').style.display = 'none';
    return;
  }
  
  document.getElementById('sessionManagement').style.display = 'block';
  
  var formData = new FormData();
  formData.append('action', 'get_sessions');
  formData.append('course_id', courseId);
  
  fetch('attendance.php', {
    method: 'POST',
    body: formData
  })
  .then(function(res) { return res.json(); })
  .then(function(data) {
    if (data.success) {
      var div = document.getElementById('sessionList');
      if (data.sessions.length > 0) {
        var html = '<table><tr><th>Date</th><th>Code</th><th>Actions</th></tr>';
        for (var i = 0; i < data.sessions.length; i++) {
          var session = data.sessions[i];
          html += '<tr>';
          html += '<td>' + session.session_date + '</td>';
          html += '<td>' + session.session_code + '</td>';
          html += '<td><button onclick="takeAttendance(' + session.id + ')">Take Attendance</button></td>';
          html += '</tr>';
        }
        html += '</table>';
        div.innerHTML = html;
      } else {
        div.innerHTML = '<p>No sessions created yet</p>';
      }
    } else {
      alert(data.message);
    }
  });
}

function createSession() {
  var courseId = document.getElementById('courseSelect').value;
  var sessionDate = document.getElementById('sessionDate').value;
  
  if (!courseId || !sessionDate) {
    alert('Please select a course and date');
    return;
  }
  
  var formData = new FormData();
  formData.append('action', 'create_session');
  formData.append('course_id', courseId);
  formData.append('session_date', sessionDate);
  
  fetch('attendance.php', {
    method: 'POST',
    body: formData
  })
  .then(function(res) { return res.json(); })
  .then(function(data) {
    if (data.success) {
      alert('Session created with code: ' + data.session_code);
      loadCourseSessions();
      document.getElementById('sessionDate').value = '';
    } else {
      alert(data.message);
    }
  });
}

function takeAttendance(sessionId) {
  // For simplicity, we'll just prompt for student ID and status
  // In a real application, this would be a more complex UI
  var studentId = prompt('Enter student ID:');
  if (!studentId) return;
  
  var status = prompt('Enter status (present/absent/late):');
  if (!status || (status !== 'present' && status !== 'absent' && status !== 'late')) {
    alert('Invalid status');
    return;
  }
  
  var formData = new FormData();
  formData.append('action', 'mark_attendance');
  formData.append('session_id', sessionId);
  formData.append('student_id', studentId);
  formData.append('status', status);
  
  fetch('attendance.php', {
    method: 'POST',
    body: formData
  })
  .then(function(res) { return res.json(); })
  .then(function(data) {
    if (data.success) {
      alert(data.message);
    } else {
      alert(data.message);
    }
  });
}

function generateReport() {
  var courseId = document.getElementById('courseSelect').value;
  if (!courseId) {
    alert('Please select a course');
    return;
  }
  
  var formData = new FormData();
  formData.append('action', 'get_attendance_report');
  formData.append('course_id', courseId);
  
  fetch('attendance.php', {
    method: 'POST',
    body: formData
  })
  .then(function(res) { return res.json(); })
  .then(function(data) {
    if (data.success) {
      var div = document.getElementById('attendanceReport');
      if (data.report.length > 0) {
        var html = '<table><tr><th>Student</th><th>Total Sessions</th><th>Present</th><th>Late</th><th>Absent</th><th>Percentage</th></tr>';
        for (var i = 0; i < data.report.length; i++) {
          var student = data.report[i];
          html += '<tr>';
          html += '<td>' + student.fullname + '</td>';
          html += '<td>' + student.total_sessions + '</td>';
          html += '<td>' + student.present_count + '</td>';
          html += '<td>' + student.late_count + '</td>';
          html += '<td>' + student.absent_count + '</td>';
          html += '<td>' + student.attendance_percentage + '%</td>';
          html += '</tr>';
        }
        html += '</table>';
        div.innerHTML = html;
      } else {
        div.innerHTML = '<p>No attendance records found</p>';
      }
    } else {
      alert(data.message);
    }
  });
}

function logout() {
  fetch('logout.php')
    .then(function(res) { return res.json(); })
    .then(function(data) {
      if (data.logout) {
        alert('Logged out successfully');
        window.location.href = 'login.html';
      }
    });
}