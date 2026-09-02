// ============================================================
//  STUDENTHUB — SUPABASE AUTHENTICATION
//  File: JS/supabase-auth.js
// ============================================================
//
//  SETUP KARNE KE LIYE:
//  1. supabase.com pe jao → apna project kholo
//  2. Settings → API → neeche dono values copy karo
//  3. SUPABASE_URL  →  "Project URL" paste karo
//  4. SUPABASE_KEY  →  "anon public" key paste karo
//
// ============================================================

const SUPABASE_URL = "**************************";   // <-- yahan apna URL paste karo
const SUPABASE_KEY = "**************************";       // <-- yahan apni anon key paste karo

// Supabase client initialize karo
const { createClient } = supabase;
const supabaseClient = createClient(SUPABASE_URL, SUPABASE_KEY);

// ============================================================
//  HELPER — Notification dikhao
// ============================================================
function showAuthNotification(message, type, duration) {
    var notif = document.getElementById("notification");
    if (!notif) return;
    notif.textContent = message;
    notif.className = "";
    notif.classList.add(type);
    notif.style.display = "block";
    setTimeout(function () {
        notif.style.display = "none";
    }, duration || 3000);
}

// ============================================================
//  HELPER — Button loading state
// ============================================================
function setButtonLoading(btnId, loading, originalText) {
    var btn = document.getElementById(btnId);
    if (!btn) return;
    if (loading) {
        btn.disabled = true;
        btn.textContent = "Please wait...";
    } else {
        btn.disabled = false;
        btn.textContent = originalText;
    }
}

// ============================================================
//  1. REGISTER — Naya student account banana
// ============================================================
var registerForm = document.getElementById("register-form");

if (registerForm) {
    registerForm.addEventListener("submit", async function (e) {
        e.preventDefault();

        // Form values read karo
        var name     = document.getElementById("name").value.trim();
        var email    = document.getElementById("email").value.trim();
        var mobile   = document.getElementById("mobile").value.trim();
        var password = document.getElementById("password").value.trim();
        var confirm  = document.getElementById("confirm").value.trim();
        var dob      = document.getElementById("dob").value;
        var course   = document.getElementById("course").value;
        var address  = document.getElementById("address").value.trim();
        var terms    = document.getElementById("terms").checked;
        var gender   = document.querySelector('input[name="gender"]:checked');

        // Selected skills collect karo
        var skillBoxes = document.querySelectorAll('input[name="skills"]:checked');
        var skills = [];
        skillBoxes.forEach(function (cb) { skills.push(cb.value); });

        // --- Validations ---
        var nameRegex     = /^[A-Za-z ]{2,50}$/;
        var emailRegex    = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        var mobileRegex   = /^[6-9][0-9]{9}$/;
        var passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).{8,}$/;

        if (!name || !email || !mobile || !password || !confirm || !dob || !course || !address) {
            showAuthNotification("Please fill in all required fields.", "notification-error", 3000);
            return;
        }
        if (!nameRegex.test(name)) {
            showAuthNotification("Name should contain only letters and spaces.", "notification-error", 3000);
            return;
        }
        if (!emailRegex.test(email)) {
            showAuthNotification("Please enter a valid email address.", "notification-error", 3000);
            return;
        }
        if (!mobileRegex.test(mobile)) {
            showAuthNotification("Please enter a valid 10-digit mobile number.", "notification-error", 3000);
            return;
        }
        if (!passwordRegex.test(password)) {
            showAuthNotification("Password must have 8+ chars with uppercase, lowercase, number & special character.", "notification-error", 4000);
            return;
        }
        if (password !== confirm) {
            showAuthNotification("Password and Confirm Password do not match.", "notification-error", 3000);
            return;
        }
        if (!gender) {
            showAuthNotification("Please select your gender.", "notification-error", 3000);
            return;
        }
        if (!terms) {
            showAuthNotification("Please accept the Terms & Conditions.", "notification-error", 3000);
            return;
        }

        setButtonLoading("submit", true, "Register");

        // --- Supabase Signup ---
        var { data, error } = await supabaseClient.auth.signUp({
            email: email,
            password: password,
            options: {
                data: {
                    full_name : name,
                    mobile    : mobile,
                    dob       : dob,
                    gender    : gender.value,
                    course    : course,
                    address   : address,
                    skills    : skills.join(", ")
                }
            }
        });

        setButtonLoading("submit", false, "Register");

        if (error) {
            // Supabase error message dikhao
            showAuthNotification("Registration failed: " + error.message, "notification-error", 4000);
            return;
        }

        // Success!
        showAuthNotification(
            "Registration successful! Please check your email to verify your account.",
            "notification-success",
            4000
        );

        // 3 second baad login page pe redirect karo
        setTimeout(function () {
            window.location.href = "login.html";
        }, 3000);
    });
}

// ============================================================
//  2. LOGIN — Existing student login
// ============================================================
var loginForm = document.getElementById("login-form");

if (loginForm) {
    loginForm.addEventListener("submit", async function (e) {
        e.preventDefault();

        var email    = document.getElementById("email").value.trim();
        var password = document.getElementById("password").value.trim();

        var emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

        if (!email || !password) {
            showAuthNotification("Please fill in all fields.", "notification-error", 3000);
            return;
        }
        if (!emailRegex.test(email)) {
            showAuthNotification("Please enter a valid email address.", "notification-error", 3000);
            return;
        }

        setButtonLoading("login-btn", true, "Login");

        // --- Supabase Sign In ---
        var { data, error } = await supabaseClient.auth.signInWithPassword({
            email: email,
            password: password
        });

        setButtonLoading("login-btn", false, "Login");

        if (error) {
            // Wrong email ya password
            if (error.message.includes("Email not confirmed")) {
                showAuthNotification("Please verify your email first. Check your inbox!", "notification-error", 4000);
            } else if (error.message.includes("Invalid login credentials")) {
                showAuthNotification("Incorrect email or password. Please try again.", "notification-error", 3000);
            } else {
                showAuthNotification("Login failed: " + error.message, "notification-error", 3000);
            }
            return;
        }

        // Success — dashboard pe bhejo
        showAuthNotification("Login successful! Redirecting...", "notification-success", 2000);
        setTimeout(function () {
            window.location.href = "dashboard.html";
        }, 1500);
    });
}

// ============================================================
//  3. FORGOT PASSWORD — Reset email bhejo
// ============================================================
var forgotLink = document.getElementById("forgot-password-link");

if (forgotLink) {
    forgotLink.addEventListener("click", async function (e) {
        e.preventDefault();

        var email = document.getElementById("email").value.trim();
        if (!email) {
            showAuthNotification("Please enter your email address first.", "notification-error", 3000);
            return;
        }

        var { error } = await supabaseClient.auth.resetPasswordForEmail(email, {
            redirectTo: window.location.origin + "/Practical-2/login.html"
        });

        if (error) {
            showAuthNotification("Error: " + error.message, "notification-error", 3000);
        } else {
            showAuthNotification("Password reset email sent! Check your inbox.", "notification-success", 4000);
        }
    });
}

// ============================================================
//  4. AUTH GUARD — Protected pages ke liye (dashboard, profile)
//     Agar logged in nahi hai to login page pe bhej do
// ============================================================
async function checkAuthAndRedirect() {
    var { data } = await supabaseClient.auth.getSession();

    if (!data.session) {
        // Session nahi hai — login pe bhejo
        window.location.href = "login.html";
        return null;
    }

    return data.session;
}

// ============================================================
//  5. SHOW USER INFO — Dashboard / Profile pe naam dikhao
// ============================================================
async function loadUserInfo() {
    var session = await checkAuthAndRedirect();
    if (!session) return;

    var user     = session.user;
    var meta     = user.user_metadata || {};
    var fullName = meta.full_name || user.email.split("@")[0];
    var email    = user.email;
    var course   = meta.course || "B.Tech";
    var avatar   = meta.avatar_url || "https://cdn-icons-png.flaticon.com/512/3135/3135715.png";

    // Dashboard welcome message & name update
    var welcomeEl = document.getElementById("user-welcome");
    if (welcomeEl) welcomeEl.textContent = "Welcome back, " + fullName + " 👋";

    var chipEl = document.getElementById("user-name-chip");
    if (chipEl) chipEl.textContent = fullName;

    var avatarChip = document.getElementById("user-avatar-chip");
    if (avatarChip) avatarChip.src = avatar;

    // Profile page elements
    var profileName = document.getElementById("profile-name");
    if (profileName) profileName.textContent = fullName;

    var profileAvatar = document.getElementById("profile-avatar");
    if (profileAvatar) profileAvatar.src = avatar;

    var profileStudentId = document.getElementById("profile-student-id");
    if (profileStudentId) profileStudentId.textContent = meta.student_id || "25DCE014";

    var profileEmail = document.getElementById("profile-email");
    if (profileEmail) profileEmail.textContent = email;

    var profileCourse = document.getElementById("profile-course");
    if (profileCourse) profileCourse.textContent = course;

    var profileSemester = document.getElementById("profile-semester");
    if (profileSemester && meta.semester) {
        profileSemester.textContent = meta.semester.includes("Semester") ? meta.semester : meta.semester + " Semester";
    }

    var profileMobile = document.getElementById("profile-mobile");
    if (profileMobile) profileMobile.textContent = meta.mobile || "—";

    var profileDob = document.getElementById("profile-dob");
    if (profileDob) profileDob.textContent = meta.dob || "—";

    var profileGender = document.getElementById("profile-gender");
    if (profileGender) profileGender.textContent = meta.gender || "—";

    var profileAddress = document.getElementById("profile-address");
    if (profileAddress) profileAddress.textContent = meta.address || "—";

    // Dynamic Skill Tags rendering on Profile
    var skillsWrap = document.getElementById("profile-skills-wrap");
    if (skillsWrap && meta.skills) {
        var skillList = meta.skills.split(",").map(function(s) { return s.trim(); }).filter(Boolean);
        if (skillList.length > 0) {
            skillsWrap.innerHTML = "";
            skillList.forEach(function(skill) {
                var span = document.createElement("span");
                span.className = "skill-tag";
                span.textContent = skill;
                skillsWrap.appendChild(span);
            });
        }
    }
}

// ============================================================
//  6. LOGOUT — Logout button / link
// ============================================================
var logoutBtn = document.getElementById("logout-btn");

if (logoutBtn) {
    logoutBtn.addEventListener("click", async function (e) {
        e.preventDefault();
        await supabaseClient.auth.signOut();
        window.location.href = "home.html";
    });
}

// ============================================================
//  7. PAGE DETECTION — Kaunsa page hai uske hisab se run karo
// ============================================================
var currentPage = window.location.pathname;

// Dashboard ya Profile pe hai to auth check karo
if (currentPage.includes("dashboard.html") || currentPage.includes("profile.html")) {
    loadUserInfo();
}

// Edit Profile page pe hai to edit logic chalaao
if (currentPage.includes("edit.html")) {
    initEditProfile();
}

// ============================================================
//  SHOW/HIDE PASSWORD — Login page eye icon
// ============================================================
var togglePasswordBtn = document.getElementById("togglePassword");
var passwordInput     = document.getElementById("password");

if (togglePasswordBtn && passwordInput) {
    togglePasswordBtn.addEventListener("click", function () {
        if (passwordInput.type === "password") {
            passwordInput.type = "text";
            togglePasswordBtn.textContent = "\uD83D\uDE48";
        } else {
            passwordInput.type = "password";
            togglePasswordBtn.textContent = "\uD83D\uDC41";
        }
    });
}

// ============================================================
//  8. EDIT PROFILE — Form load + save + photo upload
// ============================================================
async function initEditProfile() {

    // Auth check — login nahi hai to redirect
    var session = await checkAuthAndRedirect();
    if (!session) return;

    var user = session.user;
    var meta = user.user_metadata || {};

    // --- Form fields mein current data bharo ---
    var f = function(id) { return document.getElementById(id); };

    if (f("edit-name"))       f("edit-name").value       = meta.full_name || "";
    if (f("edit-student-id")) f("edit-student-id").value = meta.student_id || "25DCE014";
    if (f("edit-email"))      f("edit-email").value      = user.email || "";
    if (f("edit-mobile"))     f("edit-mobile").value     = meta.mobile || "";
    if (f("edit-dob"))        f("edit-dob").value        = meta.dob || "";
    if (f("edit-address"))    f("edit-address").value    = meta.address || "";
    if (f("edit-skills"))     f("edit-skills").value     = meta.skills || "";

    // Course select
    if (f("edit-course") && meta.course) {
        var courseOpts = f("edit-course").options;
        for (var i = 0; i < courseOpts.length; i++) {
            if (courseOpts[i].value === meta.course) {
                f("edit-course").selectedIndex = i;
                break;
            }
        }
    }

    // Semester select
    if (f("edit-semester") && meta.semester) {
        var semOpts = f("edit-semester").options;
        for (var j = 0; j < semOpts.length; j++) {
            if (semOpts[j].value === meta.semester) {
                f("edit-semester").selectedIndex = j;
                break;
            }
        }
    }

    // Gender radio
    if (meta.gender) {
        var radios = document.querySelectorAll('input[name="gender"]');
        radios.forEach(function(r) {
            if (r.value === meta.gender) r.checked = true;
        });
    }

    // Profile photo load karo agar hai to
    if (meta.avatar_url && f("avatar-preview")) {
        f("avatar-preview").src = meta.avatar_url;
    }

    // ------------------------------------------------
    // PHOTO PREVIEW — file select hone pe preview dikho
    // ------------------------------------------------
    var photoInput = document.getElementById("photo-input");
    if (photoInput) {
        photoInput.addEventListener("change", function () {
            var file = photoInput.files[0];
            if (!file) return;

            // Size check (2MB)
            if (file.size > 2 * 1024 * 1024) {
                showAuthNotification("Photo size must be less than 2MB.", "notification-error", 3000);
                photoInput.value = "";
                return;
            }

            // Local preview dikho
            var reader = new FileReader();
            reader.onload = function (e) {
                var preview = document.getElementById("avatar-preview");
                if (preview) preview.src = e.target.result;
            };
            reader.readAsDataURL(file);
        });
    }

    // ------------------------------------------------
    // FORM SUBMIT — Save changes to Supabase
    // ------------------------------------------------
    var editForm = document.getElementById("edit-profile-form");
    if (!editForm) return;

    editForm.addEventListener("submit", async function (e) {
        e.preventDefault();

        var saveBtn = document.getElementById("save-btn");
        if (saveBtn) {
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';
        }

        var name       = f("edit-name")       ? f("edit-name").value.trim() : "";
        var studentId  = f("edit-student-id") ? f("edit-student-id").value.trim() : "";
        var mobile     = f("edit-mobile")     ? f("edit-mobile").value.trim() : "";
        var dob        = f("edit-dob")        ? f("edit-dob").value : "";
        var course     = f("edit-course")     ? f("edit-course").value : "";
        var semester   = f("edit-semester")   ? f("edit-semester").value : "";
        var address    = f("edit-address")    ? f("edit-address").value.trim() : "";
        var skills     = f("edit-skills")     ? f("edit-skills").value.trim() : "";
        var gender     = document.querySelector('input[name="gender"]:checked');

        var updatedMeta = {
            full_name  : name,
            student_id : studentId,
            mobile     : mobile,
            dob        : dob,
            course     : course,
            semester   : semester,
            address    : address,
            skills     : skills,
            gender     : gender ? gender.value : (meta.gender || "")
        };

        // ---- PHOTO UPLOAD (agar naya photo select kiya ho) ----
        var photoFile = photoInput ? photoInput.files[0] : null;

        if (photoFile) {
            var progressDiv = document.getElementById("upload-progress");
            var progressFill = document.getElementById("progress-fill");
            var uploadStatus = document.getElementById("upload-status");
            if (progressDiv) progressDiv.style.display = "block";

            var prog = 0;
            var progInterval = setInterval(function() {
                prog = Math.min(prog + 20, 90);
                if (progressFill) progressFill.style.width = prog + "%";
            }, 150);

            var fileExt = photoFile.name.split(".").pop();
            var fileName = "avatar_" + user.id + "_" + Date.now() + "." + fileExt;

            try {
                // Pehle Supabase Storage 'avatars' bucket try karo
                var { data: uploadData, error: uploadError } = await supabaseClient.storage
                    .from("avatars")
                    .upload(fileName, photoFile, {
                        cacheControl: "3600",
                        upsert: true
                    });

                if (!uploadError && uploadData) {
                    var { data: urlData } = supabaseClient.storage
                        .from("avatars")
                        .getPublicUrl(fileName);
                    updatedMeta.avatar_url = urlData.publicUrl;
                } else {
                    // Agar Storage bucket nahi bana to base64 fallback save karo
                    var base64Photo = await new Promise(function (resolve) {
                        var reader = new FileReader();
                        reader.onload = function (evt) { resolve(evt.target.result); };
                        reader.readAsDataURL(photoFile);
                    });
                    updatedMeta.avatar_url = base64Photo;
                }
            } catch (err) {
                var base64Photo = await new Promise(function (resolve) {
                    var reader = new FileReader();
                    reader.onload = function (evt) { resolve(evt.target.result); };
                    reader.readAsDataURL(photoFile);
                });
                updatedMeta.avatar_url = base64Photo;
            }

            clearInterval(progInterval);
            if (progressFill) progressFill.style.width = "100%";
            if (uploadStatus) uploadStatus.textContent = "Photo processed!";
        }

        // ---- UPDATE Supabase Auth user_metadata ----
        var { error: updateError } = await supabaseClient.auth.updateUser({
            data: updatedMeta
        });

        if (saveBtn) {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Save Changes';
        }

        if (updateError) {
            showAuthNotification("Save failed: " + updateError.message, "notification-error", 4000);
            return;
        }

        // Success!
        showAuthNotification(
            "Profile updated successfully! Redirecting...",
            "notification-success",
            2500
        );

        setTimeout(function () {
            window.location.href = "profile.html";
        }, 2200);
    });
}
