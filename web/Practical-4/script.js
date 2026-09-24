/**
 * Practical 4: JavaScript DOM Manipulation, Event Handling & UI Interactivity
 * Course: Web Development Framework (WDF)
 */

document.addEventListener("DOMContentLoaded", function () {
  
  // ==========================================
  // 1. THEME SWITCHER (Dark / Light Mode + localStorage)
  // ==========================================
  const themeToggleBtn = document.getElementById("themeToggleBtn");
  const brandLogo = document.getElementById("brand-logo");

  function applyTheme(theme) {
    if (theme === "dark") {
      document.body.classList.add("dark-mode");
      if (themeToggleBtn) themeToggleBtn.innerHTML = '<i class="fa-solid fa-sun" style="color:#facc15;"></i>';
      if (brandLogo) brandLogo.src = "../IMAGES/logo-white.svg";
    } else {
      document.body.classList.remove("dark-mode");
      if (themeToggleBtn) themeToggleBtn.innerHTML = '<i class="fa-solid fa-moon"></i>';
      if (brandLogo) brandLogo.src = "../IMAGES/logo.svg";
    }
  }

  // Restore saved theme from localStorage
  const savedTheme = localStorage.getItem("studenthub_theme") || "light";
  applyTheme(savedTheme);

  if (themeToggleBtn) {
    themeToggleBtn.addEventListener("click", function () {
      const isDark = document.body.classList.contains("dark-mode");
      const nextTheme = isDark ? "light" : "dark";
      applyTheme(nextTheme);
      localStorage.setItem("studenthub_theme", nextTheme);
      triggerToast(`Switched to ${nextTheme.toUpperCase()} mode!`, "info");
    });
  }

  // ==========================================
  // 2. HAMBURGER MENU / RESPONSIVE DRAWER
  // ==========================================
  const hamburgerBtn = document.getElementById("hamburgerBtn");
  const closeDrawerBtn = document.getElementById("closeDrawerBtn");
  const mobileDrawer = document.getElementById("mobileDrawer");
  const drawerOverlay = document.getElementById("drawerOverlay");

  function openDrawer() {
    mobileDrawer.classList.add("open");
    drawerOverlay.classList.add("active");
  }

  function closeDrawer() {
    mobileDrawer.classList.remove("open");
    drawerOverlay.classList.remove("active");
  }

  if (hamburgerBtn) hamburgerBtn.addEventListener("click", openDrawer);
  if (closeDrawerBtn) closeDrawerBtn.addEventListener("click", closeDrawer);
  if (drawerOverlay) drawerOverlay.addEventListener("click", closeDrawer);

  // ==========================================
  // 3. IMAGE / CONTENT SLIDER
  // ==========================================
  const slides = document.querySelectorAll(".slide");
  const dots = document.querySelectorAll(".dot");
  const prevSlideBtn = document.getElementById("prevSlideBtn");
  const nextSlideBtn = document.getElementById("nextSlideBtn");
  let currentSlide = 0;
  let slideInterval = null;

  function showSlide(index) {
    if (!slides.length) return;
    slides.forEach((slide) => slide.classList.remove("active"));
    dots.forEach((dot) => dot.classList.remove("active"));

    currentSlide = (index + slides.length) % slides.length;
    slides[currentSlide].classList.add("active");
    if (dots[currentSlide]) dots[currentSlide].classList.add("active");
  }

  function nextSlide() {
    showSlide(currentSlide + 1);
  }

  function prevSlide() {
    showSlide(currentSlide - 1);
  }

  if (nextSlideBtn) nextSlideBtn.addEventListener("click", nextSlide);
  if (prevSlideBtn) prevSlideBtn.addEventListener("click", prevSlide);

  dots.forEach((dot, index) => {
    dot.addEventListener("click", () => showSlide(index));
  });

  // Auto-play Slider (Every 4 seconds)
  function startAutoPlay() {
    slideInterval = setInterval(nextSlide, 4000);
  }

  function stopAutoPlay() {
    clearInterval(slideInterval);
  }

  const sliderContainer = document.getElementById("campusSlider");
  if (sliderContainer) {
    sliderContainer.addEventListener("mouseenter", stopAutoPlay);
    sliderContainer.addEventListener("mouseleave", startAutoPlay);
    startAutoPlay();
  }

  // ==========================================
  // 4. COLLAPSIBLE FAQ ACCORDION
  // ==========================================
  const faqItems = document.querySelectorAll(".faq-item");

  faqItems.forEach((item) => {
    const questionBtn = item.querySelector(".faq-question");
    questionBtn.addEventListener("click", function () {
      const isOpen = item.classList.contains("active");

      // Close all other accordion items
      faqItems.forEach((i) => i.classList.remove("active"));

      // Toggle clicked item
      if (!isOpen) {
        item.classList.add("active");
      }
    });
  });

  // ==========================================
  // 5. MODAL POPUP DIALOG
  // ==========================================
  const openModalBtn = document.getElementById("openModalBtn");
  const closeModalBtn = document.getElementById("closeModalBtn");
  const eventModal = document.getElementById("eventModal");
  const modalForm = document.getElementById("modalForm");

  function openModal() {
    if (eventModal) eventModal.classList.add("active");
  }

  function closeModal() {
    if (eventModal) eventModal.classList.remove("active");
  }

  if (openModalBtn) openModalBtn.addEventListener("click", openModal);
  if (closeModalBtn) closeModalBtn.addEventListener("click", closeModal);

  // Close modal when clicking backdrop
  if (eventModal) {
    eventModal.addEventListener("click", function (e) {
      if (e.target === eventModal) closeModal();
    });
  }

  // Keyboard accessibility: ESC key to close modal
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape" && eventModal && eventModal.classList.contains("active")) {
      closeModal();
    }
  });

  if (modalForm) {
    modalForm.addEventListener("submit", function (e) {
      e.preventDefault();
      const studentName = document.getElementById("modalStudentName").value;
      closeModal();
      triggerToast(`Registration confirmed for ${studentName}!`, "success");
      modalForm.reset();
    });
  }

});

// ==========================================
// 6. TOAST NOTIFICATION BANNER SYSTEM
// ==========================================
function triggerToast(message, type = "info", duration = 3500) {
  const container = document.getElementById("toast-container");
  if (!container) return;

  const toast = document.createElement("div");
  toast.className = `toast toast-${type}`;

  let icon = "fa-circle-info";
  if (type === "success") icon = "fa-circle-check";
  if (type === "error") icon = "fa-triangle-exclamation";
  if (type === "warning") icon = "fa-bell";

  toast.innerHTML = `<i class="fa-solid ${icon}"></i> <span>${message}</span>`;
  container.appendChild(toast);

  setTimeout(() => {
    toast.style.opacity = "0";
    toast.style.transform = "translateX(100%)";
    toast.style.transition = "all 0.3s ease";
    setTimeout(() => toast.remove(), 300);
  }, duration);
}