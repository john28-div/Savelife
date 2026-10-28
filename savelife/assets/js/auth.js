// Show / hide password buttons
document.querySelectorAll('.toggle').forEach(function (btn) {
  btn.addEventListener('click', function () {
    var input = document.getElementById(btn.dataset.target);
    var showing = input.type === 'text';
    input.type = showing ? 'password' : 'text';
    btn.textContent = showing ? 'Show' : 'Hide';
  });
});

// Client-side check on the sign up form (the server checks again)
var reg = document.getElementById('registerForm');
if (reg) {
  reg.addEventListener('submit', function (e) {
    var pw = document.getElementById('password').value;
    var cf = document.getElementById('confirm_password').value;
    var msg = '';
    if (pw.length < 8) msg = 'Password must be at least 8 characters.';
    else if (pw !== cf) msg = 'Passwords do not match.';
    if (msg) {
      e.preventDefault();
      var box = document.getElementById('clientError');
      if (!box) {
        box = document.createElement('div');
        box.id = 'clientError';
        box.className = 'alert error';
        box.setAttribute('role', 'alert');
        reg.parentNode.insertBefore(box, reg);
      }
      box.textContent = msg;
    }
  });
}
