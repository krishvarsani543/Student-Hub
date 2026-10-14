
const loginForm = document.getElementById("loginForm");

loginForm.addEventListener("submit", function (event) {
    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value;
    const errorMessage = document.getElementById("errormessage");

    const emailregex = /^[A-Za-z0-9._%+-]+@charusat\.edu\.in$/;
    const passwordregex = /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d).{8,}$/;

    errorMessage.textContent = "";

    if (!emailregex.test(email)) {
        event.preventDefault();
        errorMessage.textContent = "Please enter a valid CHARUSAT email.";
        return;
    }

    if (!passwordregex.test(password)) {
        event.preventDefault();
        errorMessage.textContent =
            "Password must contain at least 8 characters, one uppercase letter, one lowercase letter and one number.";
        return;
    }

    
});

const urlParams = new URLSearchParams(window.location.search);

const registrationModal = document.getElementById("registrationModal");
const closeRegistrationModal =
    document.getElementById("closeRegistrationModal");
const okRegistrationModal =
    document.getElementById("okRegistrationModal");

if (urlParams.get("registered") === "success") {
    registrationModal.style.display = "flex";
}

closeRegistrationModal.addEventListener("click", function () {
    registrationModal.style.display = "none";
});

okRegistrationModal.addEventListener("click", function () {
    registrationModal.style.display = "none";
});