// const registerform = document.getElementById("registerform");

// registerform.addEventListener("submit" , function(event){
//     event.preventDefault();

//     const name = document.getElementById("name");
//     const email = document.getElementById("email");
//     const mobile = document.getElementById("mobile");
//     const password = document.getElementById("password");
//     const confirmpassword = document.getElementById("confirm");

//     const nameregex = /^[A-Za-z ]+$/;

//     const emailregex = /^[0-9]{2}d[a-xA-Z]{2}[0-9]{3}@charusat\.edu\.in$/;

//     const mobileregex = /^[0-9]{10}$/;

//     const passwordregex = /^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])[A-Za-z]{8,}$/;

//     if(!nameregex.test(name)){
//         alert("name should be contain letters and spaces");
//         return;
//     }

//     if(!emailregex.test(email)){
//         alert("please enter a valid CHARUSAT email ");
//         return;
//     }

//     if(!mobileregex.test(mobile)){
//         alert("mobile number must be contain exactly 10  digits ");
//         return;
//     }

//     if(!passwordregex.test(password)){
//         alert("password must be contain at leaste 8 characters, 1 capital letter, 1 number");
//         return;
//     }
//     if(password!=confirmpassword){
//         alert("password and confiempassword does not match");
//         return;
//     }

//     alert("registration successful!");
//     registerform.submit();
// });




const registerForm = document.getElementById("registerForm");
const validationPopup = document.getElementById("validationPopup");
const popupTitle = document.getElementById("popupTitle");
const popupMessage = document.getElementById("popupMessage");
const popupOk = document.getElementById("popupOk");
const closePopup = document.getElementById("closePopup");

function showPopup(title, message, success = false) {
    popupTitle.textContent = title;
    popupMessage.textContent = message;
    popupTitle.style.color = success ? "green" : "#dc3545";
    validationPopup.style.display = "flex";
}

function hidePopup() {
    validationPopup.style.display = "none";
}

closePopup.addEventListener("click", hidePopup);

popupOk.addEventListener("click", hidePopup);

registerForm.addEventListener("submit", async function (event) {
    event.preventDefault();

    const name = document.getElementById("name").value.trim();
    const email = document.getElementById("email").value.trim();
    const mobile = document.getElementById("mobile").value.trim();
    const password = document.getElementById("password").value;
    const confirmPassword = document.getElementById("confirm").value;

    const nameRegex = /^[A-Za-z ]+$/;
    const emailRegex = /^[0-9]{2}d[a-zA-Z]{2}[0-9]{3}@charusat\.edu\.in$/;
    const mobileRegex = /^[0-9]{10}$/;
    const passwordRegex = /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d).{8,}$/;

    if (!nameRegex.test(name)) {
        showPopup("Invalid Name", "Name should contain only letters and spaces.");
        return;
    }

    if (!emailRegex.test(email)) {
        showPopup("Invalid Email", "Please enter a valid CHARUSAT email.");
        return;
    }

    if (!mobileRegex.test(mobile)) {
        showPopup("Invalid Mobile Number", "Mobile number must contain exactly 10 digits.");
        return;
    }

    if (!passwordRegex.test(password)) {
        showPopup("Invalid Password", "Password must contain at least 8 characters, 1 uppercase letter, 1 lowercase letter and 1 number.");
        return;
    }

    if (password !== confirmPassword) {
        showPopup("Password Mismatch", "Password and Confirm Password do not match.");
        return;
    }

    const genderSelected = document.querySelector('input[name="gender"]:checked');
    if (!genderSelected) {
        showPopup("Gender Required", "Please select your gender.");
        return;
    }

    const courseSelect = document.getElementById("course");
    if (!courseSelect || !courseSelect.value) {
        showPopup("Course Required", "Please select a course.");
        return;
    }

    const countryVal = document.getElementById("country").value;
    const stateVal = document.getElementById("state").value;
    const cityVal = document.getElementById("city").value;
    if (!countryVal || !stateVal || !cityVal) {
        showPopup("Location Required", "Please select Country, State, and City.");
        return;
    }

    const checkedSkills = document.querySelectorAll('input[name="skill"]:checked, input[name="skill[]"]:checked');
    if (checkedSkills.length === 0) {
        showPopup("Skill Required", "Please select at least one skill.");
        return;
    }

    const addressVal = document.getElementById("address").value.trim();
    if (!addressVal) {
        showPopup("Address Required", "Please enter your address.");
        return;
    }

    const termsChecked = document.getElementById("terms").checked;
    if (!termsChecked) {
        showPopup("Terms Required", "You must agree to the Terms & Conditions.");
        return;
    }

    const formData = new FormData(registerForm);

    try {
        const response = await fetch("../Practical-7&8&9/check_email.php", {
            method: "POST",
            body: formData
        });

        const result = await response.json();

        if (result.status === "duplicate") {
            showPopup("Duplicate Email", result.message);
            return;
        }

        if (!response.ok || result.status !== "available") {
            showPopup("Email Check Error", result.message || "Unable to verify email.");
            return;
        }

        registerForm.action = "../Practical-7&8&9/register.php";
        registerForm.submit();
    } catch (error) {
        showPopup("Connection Error", "Unable to connect to database or check email. Please try again.");
    }
});

const countrySelect = document.getElementById("country");
const stateSelect = document.getElementById("state");
const citySelect = document.getElementById("city");

let locationData = {};

fetch("../Practical-6/register.json")
    .then(function (response) {
        if (!response.ok) {
            throw new Error("Failed to load register.json");
        }
        return response.json();
    })
    .then(function (data) {
        locationData = data;
        console.log("Location data loaded successfully");
    })
    .catch(function (error) {
        console.error("Error loading location data:", error);
    });

countrySelect.addEventListener("change", function () {
    const country = countrySelect.value;

    stateSelect.innerHTML = '<option value="">Select State</option>';
    citySelect.innerHTML = '<option value="">Select City</option>';
    citySelect.disabled = true;

    if (country === "") {
        stateSelect.disabled = true;
        return;
    }

    stateSelect.disabled = false;

    const states = Object.keys(locationData[country] || {});

    states.forEach(function (state) {
        const option = document.createElement("option");
        option.value = state;
        option.textContent = state;
        stateSelect.appendChild(option);
    });
});

stateSelect.addEventListener("change", function () {
    const country = countrySelect.value;
    const state = stateSelect.value;

    citySelect.innerHTML = '<option value="">Select City</option>';

    if (state === "") {
        citySelect.disabled = true;
        return;
    }

    citySelect.disabled = false;

    const cities = (locationData[country] || {})[state] || [];

    cities.forEach(function (city) {
        const option = document.createElement("option");
        option.value = city;
        option.textContent = city;
        citySelect.appendChild(option);
    });
});