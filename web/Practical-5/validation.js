/**
 * Practical 5: Registration Form with Frontend Validation & Canvas CAPTCHA
 * Course: Web Development Framework (WDF)
 */

document.addEventListener("DOMContentLoaded", function () {
  
  // Elements
  const form = document.getElementById("registrationForm");
  const fullName = document.getElementById("fullName");
  const email = document.getElementById("email");
  const mobile = document.getElementById("mobile");
  const dob = document.getElementById("dob");
  const course = document.getElementById("course");
  const academicYear = document.getElementById("academicYear");
  const password = document.getElementById("password");
  const confirmPassword = document.getElementById("confirmPassword");
  const address = document.getElementById("address");
  const termsCheckbox = document.getElementById("termsCheckbox");
  const captchaInput = document.getElementById("captchaInput");
  const refreshCaptchaBtn = document.getElementById("refreshCaptchaBtn");
  const togglePasswordBtn = document.getElementById("togglePasswordBtn");
  const successAlert = document.getElementById("formSuccessAlert");

  // Regex Patterns
  const nameRegex = /^[A-Za-z\s]{3,50}$/;
  const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
  const mobileRegex = /^[6-9]\d{9}$/; // 10 digit Indian number starting with 6, 7, 8, 9
  const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#^()_-])[A-Za-z\d@$!%*?&#^()_-]{8,}$/;

  // ==========================================
  // 1. CANVAS CAPTCHA GENERATION
  // ==========================================
  let generatedCaptcha = "";

  function generateCaptcha() {
    const chars = "ABCDEFGHJKLMNPQRSTUVWXYZ23456789"; // Removed ambiguous chars I, O, 0, 1
    generatedCaptcha = "";
    for (let i = 0; i < 5; i++) {
      generatedCaptcha += chars.charAt(Math.floor(Math.random() * chars.length));
    }

    const canvas = document.getElementById("captchaCanvas");
    if (!canvas) return;
    const ctx = canvas.getContext("2d");

    // Clear canvas
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    // Background noise
    ctx.fillStyle = "#f1f5f9";
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    // Add random noise lines
    for (let i = 0; i < 4; i++) {
      ctx.strokeStyle = `rgba(${Math.random() * 100}, ${Math.random() * 100}, ${Math.random() * 100}, 0.3)`;
      ctx.beginPath();
      ctx.moveTo(Math.random() * canvas.width, Math.random() * canvas.height);
      ctx.lineTo(Math.random() * canvas.width, Math.random() * canvas.height);
      ctx.stroke();
    }

    // Draw characters with rotation & color variance
    ctx.font = "bold 24px 'Inter', sans-serif";
    ctx.textBaseline = "middle";

    for (let i = 0; i < generatedCaptcha.length; i++) {
      const char = generatedCaptcha[i];
      ctx.save();
      const x = 24 + i * 28;
      const y = canvas.height / 2;
      const angle = (Math.random() - 0.5) * 0.4;
      ctx.translate(x, y);
      ctx.rotate(angle);
      ctx.fillStyle = i % 2 === 0 ? "#1e3d59" : "#2f5f80";
      ctx.fillText(char, -10, 2);
      ctx.restore();
    }
  }

  generateCaptcha();
  if (refreshCaptchaBtn) refreshCaptchaBtn.addEventListener("click", generateCaptcha);

  // ==========================================
  // 2. PASSWORD TOGGLE & STRENGTH EVALUATION
  // ==========================================
  if (togglePasswordBtn && password) {
    togglePasswordBtn.addEventListener("click", function () {
      const isPassword = password.type === "password";
      password.type = isPassword ? "text" : "password";
      togglePasswordBtn.innerHTML = isPassword ? '<i class="fa-solid fa-eye-slash"></i>' : '<i class="fa-solid fa-eye"></i>';
    });
  }

  function evaluatePasswordStrength(val) {
    const strengthBar = document.getElementById("strengthBar");
    const strengthLabel = document.getElementById("strengthLabel");

    let score = 0;
    if (val.length >= 8) score++;
    if (/[a-z]/.test(val) && /[A-Z]/.test(val)) score++;
    if (/\d/.test(val)) score++;
    if (/[@$!%*?&#^()_-]/.test(val)) score++;

    if (val.length === 0) {
      strengthBar.style.width = "0%";
      strengthLabel.innerHTML = "Password Strength: <span>None</span>";
      strengthLabel.querySelector("span").style.color = "inherit";
    } else if (score <= 1) {
      strengthBar.style.width = "25%";
      strengthBar.style.backgroundColor = "#ef4444";
      strengthLabel.innerHTML = 'Password Strength: <span style="color:#ef4444;">Weak</span>';
    } else if (score === 2 || score === 3) {
      strengthBar.style.width = "65%";
      strengthBar.style.backgroundColor = "#f59e0b";
      strengthLabel.innerHTML = 'Password Strength: <span style="color:#f59e0b;">Medium</span>';
    } else {
      strengthBar.style.width = "100%";
      strengthBar.style.backgroundColor = "#10b981";
      strengthLabel.innerHTML = 'Password Strength: <span style="color:#10b981;">Strong</span>';
    }
  }

  if (password) {
    password.addEventListener("input", function () {
      evaluatePasswordStrength(password.value);
      validatePassword();
    });
  }

  // ==========================================
  // 3. FIELD VALIDATION HELPERS
  // ==========================================
  function setStatus(inputEl, errorEl, isValid, message = "") {
    const group = inputEl.closest(".form-group") || inputEl.closest(".captcha-box");
    if (!group) return;

    if (isValid) {
      group.classList.remove("error");
      group.classList.add("success");
      if (errorEl) errorEl.textContent = "";
    } else {
      group.classList.remove("success");
      group.classList.add("error");
      if (errorEl) errorEl.textContent = message;
    }
  }

  function validateName() {
    const val = fullName.value.trim();
    if (!val) {
      setStatus(fullName, document.getElementById("nameError"), false, "Full Name is required.");
      return false;
    }
    if (!nameRegex.test(val)) {
      setStatus(fullName, document.getElementById("nameError"), false, "Name must contain only letters and spaces (3-50 chars).");
      return false;
    }
    setStatus(fullName, document.getElementById("nameError"), true);
    return true;
  }

  function validateEmail() {
    const val = email.value.trim();
    if (!val) {
      setStatus(email, document.getElementById("emailError"), false, "Email address is required.");
      return false;
    }
    if (!emailRegex.test(val)) {
      setStatus(email, document.getElementById("emailError"), false, "Please enter a valid email (e.g. user@domain.com).");
      return false;
    }
    setStatus(email, document.getElementById("emailError"), true);
    return true;
  }

  function validateMobile() {
    const val = mobile.value.trim();
    if (!val) {
      setStatus(mobile, document.getElementById("mobileError"), false, "Mobile number is required.");
      return false;
    }
    if (!mobileRegex.test(val)) {
      setStatus(mobile, document.getElementById("mobileError"), false, "Enter a valid 10-digit number starting with 6, 7, 8, or 9.");
      return false;
    }
    setStatus(mobile, document.getElementById("mobileError"), true);
    return true;
  }

  function validateDob() {
    const val = dob.value;
    if (!val) {
      setStatus(dob, document.getElementById("dobError"), false, "Please select your date of birth.");
      return false;
    }
    const birthYear = new Date(val).getFullYear();
    const currentYear = new Date().getFullYear();
    const age = currentYear - birthYear;
    if (age < 15 || age > 65) {
      setStatus(dob, document.getElementById("dobError"), false, "Student age must be between 15 and 65 years.");
      return false;
    }
    setStatus(dob, document.getElementById("dobError"), true);
    return true;
  }

  function validateCourse() {
    if (!course.value) {
      setStatus(course, document.getElementById("courseError"), false, "Please select an enrolled degree course.");
      return false;
    }
    setStatus(course, document.getElementById("courseError"), true);
    return true;
  }

  function validateYear() {
    if (!academicYear.value) {
      setStatus(academicYear, document.getElementById("yearError"), false, "Please select your academic year.");
      return false;
    }
    setStatus(academicYear, document.getElementById("yearError"), true);
    return true;
  }

  function validateGender() {
    const genderChecked = document.querySelector('input[name="gender"]:checked');
    const genderError = document.getElementById("genderError");
    const groupGender = document.getElementById("group-gender");
    if (!genderChecked) {
      groupGender.classList.add("error");
      genderError.textContent = "Please select your gender.";
      return false;
    }
    groupGender.classList.remove("error");
    genderError.textContent = "";
    return true;
  }

  function validatePassword() {
    const val = password.value;
    if (!val) {
      setStatus(password, document.getElementById("passwordError"), false, "Password is required.");
      return false;
    }
    if (!passwordRegex.test(val)) {
      setStatus(password, document.getElementById("passwordError"), false, "Password must be at least 8 chars and include upper, lower, number, and special character.");
      return false;
    }
    setStatus(password, document.getElementById("passwordError"), true);
    return true;
  }

  function validateConfirmPassword() {
    const val = confirmPassword.value;
    if (!val) {
      setStatus(confirmPassword, document.getElementById("confirmError"), false, "Please confirm your password.");
      return false;
    }
    if (val !== password.value) {
      setStatus(confirmPassword, document.getElementById("confirmError"), false, "Passwords do not match.");
      return false;
    }
    setStatus(confirmPassword, document.getElementById("confirmError"), true);
    return true;
  }

  function validateAddress() {
    const val = address.value.trim();
    if (!val || val.length < 10) {
      setStatus(address, document.getElementById("addressError"), false, "Please provide complete address (min. 10 characters).");
      return false;
    }
    setStatus(address, document.getElementById("addressError"), true);
    return true;
  }

  function validateCaptcha() {
    const val = captchaInput.value.trim().toUpperCase();
    const captchaError = document.getElementById("captchaError");
    if (!val) {
      captchaError.textContent = "Please enter the CAPTCHA code.";
      return false;
    }
    if (val !== generatedCaptcha) {
      captchaError.textContent = "Incorrect CAPTCHA code. Please try again.";
      generateCaptcha();
      captchaInput.value = "";
      return false;
    }
    captchaError.textContent = "";
    return true;
  }

  function validateTerms() {
    const termsError = document.getElementById("termsError");
    if (!termsCheckbox.checked) {
      termsError.textContent = "You must accept the Terms and Privacy Policy.";
      return false;
    }
    termsError.textContent = "";
    return true;
  }

  // ==========================================
  // 4. REAL-TIME EVENT LISTENERS (input & blur)
  // ==========================================
  fullName.addEventListener("input", validateName);
  fullName.addEventListener("blur", validateName);

  email.addEventListener("input", validateEmail);
  email.addEventListener("blur", validateEmail);

  mobile.addEventListener("input", validateMobile);
  mobile.addEventListener("blur", validateMobile);

  dob.addEventListener("change", validateDob);
  course.addEventListener("change", validateCourse);
  academicYear.addEventListener("change", validateYear);

  confirmPassword.addEventListener("input", validateConfirmPassword);
  confirmPassword.addEventListener("blur", validateConfirmPassword);

  address.addEventListener("input", validateAddress);
  termsCheckbox.addEventListener("change", validateTerms);

  // ==========================================
  // 5. FORM SUBMISSION
  // ==========================================
  form.addEventListener("submit", function (e) {
    e.preventDefault();

    const isNameValid = validateName();
    const isEmailValid = validateEmail();
    const isMobileValid = validateMobile();
    const isDobValid = validateDob();
    const isCourseValid = validateCourse();
    const isYearValid = validateYear();
    const isGenderValid = validateGender();
    const isPassValid = validatePassword();
    const isConfirmValid = validateConfirmPassword();
    const isAddressValid = validateAddress();
    const isCaptchaValid = validateCaptcha();
    const isTermsValid = validateTerms();

    const isFormValid =
      isNameValid &&
      isEmailValid &&
      isMobileValid &&
      isDobValid &&
      isCourseValid &&
      isYearValid &&
      isGenderValid &&
      isPassValid &&
      isConfirmValid &&
      isAddressValid &&
      isCaptchaValid &&
      isTermsValid;

    if (isFormValid) {
      successAlert.style.display = "flex";
      successAlert.scrollIntoView({ behavior: "smooth", block: "center" });

      // Save dummy student profile locally
      const studentData = {
        name: fullName.value.trim(),
        email: email.value.trim(),
        mobile: mobile.value.trim(),
        course: course.value,
        year: academicYear.value,
      };
      localStorage.setItem("studenthub_registered_user", JSON.stringify(studentData));

      setTimeout(function () {
        window.location.href = "../Practical-2/dashboard.html";
      }, 2500);
    } else {
      // Focus first error field
      const firstError = document.querySelector(".form-group.error input, .form-group.error select, .form-group.error textarea");
      if (firstError) firstError.focus();
    }
  });

  form.addEventListener("reset", function () {
    setTimeout(function () {
      generateCaptcha();
      evaluatePasswordStrength("");
      document.querySelectorAll(".form-group").forEach((g) => g.classList.remove("success", "error"));
      document.querySelectorAll(".error-msg").forEach((e) => (e.textContent = ""));
      successAlert.style.display = "none";
    }, 50);
  });

});
