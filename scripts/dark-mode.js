// Toggle Switch
const themeToggle = document.getElementById('themeToggle');

themeToggle.addEventListener('change', function () {
  if (themeToggle.checked) {
    document.body.classList.add('light-mode');
    localStorage.setItem('theme', 'light-mode');
  } else {
    document.body.classList.remove('light-mode');
    localStorage.setItem('theme', 'dark-mode');
  }
});

// Check Local Storage for Theme
const storedTheme = localStorage.getItem('theme');

if (storedTheme === 'light-mode') {
  document.body.classList.add('light-mode');
  themeToggle.checked = true;
} else {
  document.body.classList.remove('light-mode');
  themeToggle.checked = false;
}