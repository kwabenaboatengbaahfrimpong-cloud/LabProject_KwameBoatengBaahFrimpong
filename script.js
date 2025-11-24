document.addEventListener('DOMContentLoaded', function() {
  var loginForm = document.querySelector('form');
  
  if (loginForm && loginForm.action.includes('login.php')) {
    loginForm.addEventListener('submit', function(e) {
      e.preventDefault();
      
      var formData = new FormData(this);
      
      fetch('login.php', {
        method: 'POST',
        body: formData
      })
      .then(function(res) { return res.json(); })
      .then(function(data) {
        if (data.success) {
          if (data.role == 'student') {
            window.location.href = 'student-dashboard.php';
          } else if (data.role == 'faculty') {
            window.location.href = 'faculty-dashboard.php';
          } else {
            window.location.href = 'intern-dashboard.php';
          }
        } else {
          alert(data.message);
        }
      });
    });
  }
  
  if (loginForm && loginForm.action.includes('signup.php')) {
    loginForm.addEventListener('submit', function(e) {
      e.preventDefault();
      
      var formData = new FormData(this);
      
      fetch('signup.php', {
        method: 'POST',
        body: formData
      })
      .then(function(res) { return res.json(); })
      .then(function(data) {
        if (data.success) {
          alert('Registration successful!');
          window.location.href = 'login.html';
        } else {
          alert(data.message);
        }
      });
    });
  }
});
