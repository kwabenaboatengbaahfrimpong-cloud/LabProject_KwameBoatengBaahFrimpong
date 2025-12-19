(function () {
  const reg = document.querySelector("#registerForm");
  if (reg) {
    reg.addEventListener("submit", function (e) {
      const email = reg.querySelector("[name=email]").value.trim();
      const pw = reg.querySelector("[name=password]").value;
      const pw2 = reg.querySelector("[name=confirm_password]").value;

      const emailOk = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
      const pwOk = /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d@$!%*#?&]{8,}$/.test(pw);

      let msg = "";
      if (!emailOk) msg = "Enter a valid email address.";
      else if (!pwOk) msg = "Password must be 8+ chars and include at least 1 letter and 1 number.";
      else if (pw !== pw2) msg = "Passwords do not match.";

      if (msg) { e.preventDefault(); alert(msg); }
    });
  }

  const sub = document.querySelector("#submissionForm");
  if (sub) {
    sub.addEventListener("submit", function (e) {
      const qty = parseFloat(sub.querySelector("[name=quantity]").value);
      if (Number.isNaN(qty) || qty <= 0 || qty > 50) {
        e.preventDefault();
        alert("Enter a valid quantity (0 - 50).");
      }
    });
  }
})();
