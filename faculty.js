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
      if (data.courses.length > 0) {
        var html = '<ul>';
        for (var i = 0; i < data.courses.length; i++) {
          html += '<li>' + data.courses[i].course_name + ' (' + data.courses[i].course_code + ')</li>';
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
