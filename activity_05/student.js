window.onload = function() {
  loadMyCourses();
  
  document.getElementById('logoutBtn').onclick = function(e) {
    e.preventDefault();
    logout();
  };
};

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
      var select = document.getElementById('studentCourseSelect');
      
      // Clear select options except the first one
      if (select) {
        select.innerHTML = '<option value="">-- Select Course --</option>';
      }
      
      if (data.courses.length > 0) {
        var html = '<ul>';
        for (var i = 0; i < data.courses.length; i++) {
          html += '<li>' + data.courses[i].course_name + ' (' + data.courses[i].course_code + ')</li>';
          // Add to select dropdown if it exists
          if (select) {
            var option = document.createElement('option');
            option.value = data.courses[i].id;
            option.text = data.courses[i].course_name + ' (' + data.courses[i].course_code + ')';
            select.appendChild(option);
          }
        }
        html += '</ul>';
        div.innerHTML = html;
      } else {
        div.innerHTML = '<p>No courses yet. Join a course to get started.</p>';
      }
    }
  });
}

function showJoinCourse() {
  document.getElementById('joinCourseModal').style.display = 'block';
}

function searchCourses() {
  var search = document.getElementById('searchCourse').value;
  var formData = new FormData();
  formData.append('action', 'search');
  formData.append('search', search);
  
  fetch('courses.php', {
    method: 'POST',
    body: formData
  })
  .then(function(res) { return res.json(); })
  .then(function(data) {
    if (data.success) {
      var div = document.getElementById('searchResults');
      if (data.courses.length > 0) {
        var html = '<ul>';
        for (var i = 0; i < data.courses.length; i++) {
          html += '<li>' + data.courses[i].course_name + ' (' + data.courses[i].course_code + ') - ' + data.courses[i].faculty_name;
          html += ' <button onclick="requestJoin(' + data.courses[i].id + ')">Request to Join</button></li>';
        }
        html += '</ul>';
        div.innerHTML = html;
      } else {
        div.innerHTML = '<p>No courses found</p>';
      }
    }
  });
}

function requestJoin(courseId) {
  var formData = new FormData();
  formData.append('action', 'request');
  formData.append('course_id', courseId);
  
  fetch('enrollments.php', {
    method: 'POST',
    body: formData
  })
  .then(function(res) { return res.json(); })
  .then(function(data) {
    if (data.success) {
      alert(data.message);
      loadMyCourses();
    } else {
      alert(data.message);
    }
  });
}

// Attendance functions
function submitAttendanceCode() {
  var code = document.getElementById('attendanceCode').value;
  
  if (!code) {
    alert('Please enter an attendance code');
    return;
  }
  
  var formData = new FormData();
  formData.append('action', 'submit_attendance_code');
  formData.append('session_code', code);
  
  fetch('attendance.php', {
    method: 'POST',
    body: formData
  })
  .then(function(res) { return res.json(); })
  .then(function(data) {
    if (data.success) {
      alert(data.message);
      document.getElementById('attendanceCode').value = '';
    } else {
      alert(data.message);
    }
  });
}

function loadMyAttendance() {
  var courseId = document.getElementById('studentCourseSelect').value;
  if (!courseId) {
    return;
  }
  
  var formData = new FormData();
  formData.append('action', 'get_my_attendance');
  formData.append('course_id', courseId);
  
  fetch('attendance.php', {
    method: 'POST',
    body: formData
  })
  .then(function(res) { return res.json(); })
  .then(function(data) {
    if (data.success) {
      // Display attendance records
      var recordsDiv = document.getElementById('attendanceRecords');
      if (data.records.length > 0) {
        var html = '<table><tr><th>Date</th><th>Status</th></tr>';
        for (var i = 0; i < data.records.length; i++) {
          var record = data.records[i];
          html += '<tr>';
          html += '<td>' + record.session_date + '</td>';
          html += '<td>' + record.status + '</td>';
          html += '</tr>';
        }
        html += '</table>';
        recordsDiv.innerHTML = html;
      } else {
        recordsDiv.innerHTML = '<p>No attendance records found</p>';
      }
      
      // Display summary
      var summaryDiv = document.getElementById('attendanceSummary');
      summaryDiv.innerHTML = '<p><strong>Attendance Summary:</strong> ' + 
                            data.summary.present_count + ' Present, ' + 
                            data.summary.late_count + ' Late, ' + 
                            data.summary.absent_count + ' Absent<br>' +
                            '<strong>Total Sessions:</strong> ' + data.summary.total_sessions + '<br>' +
                            '<strong>Attendance Percentage:</strong> ' + data.summary.attendance_percentage + '%</p>';
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