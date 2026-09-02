// ========================
// DARK / LIGHT MODE TOGGLE
// ========================

var themeToggleBtn = document.getElementById("themeToggle");

// Load saved theme from localStorage
if (localStorage.getItem("theme") === "dark") {
    document.body.classList.add("dark-mode");
    if (themeToggleBtn) themeToggleBtn.textContent = "☀️";
} else {
    if (themeToggleBtn) themeToggleBtn.textContent = "🌙";
}

if (themeToggleBtn) {
    themeToggleBtn.addEventListener("click", function () {
        document.body.classList.toggle("dark-mode");

        if (document.body.classList.contains("dark-mode")) {
            themeToggleBtn.textContent = "☀️";
            localStorage.setItem("theme", "dark");
        } else {
            themeToggleBtn.textContent = "🌙";
            localStorage.setItem("theme", "light");
        }
    });
}

// ========================
// CAMPUS SLIDER
// ========================

var currentSlide = 0;

function moveSlide(direction) {
    var slides = document.querySelectorAll(".slide");
    var dots = document.querySelectorAll(".dot");

    if (slides.length === 0) return;

    slides[currentSlide].classList.remove("active");
    dots[currentSlide].classList.remove("active");

    currentSlide = (currentSlide + direction + slides.length) % slides.length;

    slides[currentSlide].classList.add("active");
    dots[currentSlide].classList.add("active");
}

function goToSlide(index) {
    var slides = document.querySelectorAll(".slide");
    var dots = document.querySelectorAll(".dot");

    if (slides.length === 0) return;

    slides[currentSlide].classList.remove("active");
    dots[currentSlide].classList.remove("active");

    currentSlide = index;

    slides[currentSlide].classList.add("active");
    dots[currentSlide].classList.add("active");
}

// Auto-slide every 4 seconds
setInterval(function () {
    moveSlide(1);
}, 4000);
