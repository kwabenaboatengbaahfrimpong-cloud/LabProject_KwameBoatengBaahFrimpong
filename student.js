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
      if (data.courses.length > 0) {
        var html = '<ul>';
        for (var i = 0; i < data.courses.length; i++) {
          html += '<li>' + data.courses[i].course_name + ' (' + data.courses[i].course_code + ')</li>';
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
