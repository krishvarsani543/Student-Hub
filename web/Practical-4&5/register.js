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

alert("register.js is working");

const registerForm = document.getElementById("registerForm");

const validationPopup = document.getElementById("validationPopup");
const popupTitle = document.getElementById("popupTitle");
const popupMessage = document.getElementById("popupMessage");
const popupOk = document.getElementById("popupOk");
const closePopup = document.getElementById("closePopup");

function showPopup(title, message, success = false) {
    popupTitle.textContent = title;
    popupMessage.textContent = message;
    validationPopup.style.display = "flex";

    if (success) {
        popupTitle.style.color = "green";
    } else {
        popupTitle.style.color = "#dc3545";
    }
}

function hidePopup() {
    validationPopup.style.display = "none";
}

closePopup.addEventListener("click", hidePopup);

popupOk.addEventListener("click", function () {
    hidePopup();
});

registerForm.addEventListener("submit", function (event) {
    event.preventDefault();

    const name = document.getElementById("name").value.trim();
    const email = document.getElementById("email").value.trim();
    const mobile = document.getElementById("mobile").value.trim();
    const password = document.getElementById("password").value;
    const confirmPassword = document.getElementById("confirm").value;

    const nameRegex = /^[A-Za-z ]+$/;

    const emailRegex =
        /^[0-9]{2}d[a-zA-Z]{2}[0-9]{3}@charusat\.edu\.in$/;

    const mobileRegex = /^[0-9]{10}$/;

    const passwordRegex =
        /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)[A-Za-z\d]{8,}$/;

    if (!nameRegex.test(name)) {
        showPopup(
            "Invalid Name",
            "Name should contain only letters and spaces."
        );
        return;
    }

    if (!emailRegex.test(email)) {
        showPopup(
            "Invalid Email",
            "Please enter a valid CHARUSAT email."
        );
        return;
    }

    if (!mobileRegex.test(mobile)) {
        showPopup(
            "Invalid Mobile Number",
            "Mobile number must contain exactly 10 digits."
        );
        return;
    }

    if (!passwordRegex.test(password)) {
        showPopup(
            "Invalid Password",
            "Password must contain at least 8 characters, 1 uppercase letter, 1 lowercase letter and 1 number."
        );
        return;
    }

    if (password !== confirmPassword) {
        showPopup(
            "Password Mismatch",
            "Password and Confirm Password do not match."
        );
        return;
    }

    showPopup(
        "Registration Successful!",
        "Your data is ready to be submitted.",
        true
    );

    popupOk.onclick = function () {
        registerForm.submit();
    };
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

    stateSelect.innerHTML =
        '<option value="">Select State</option>';

    citySelect.innerHTML =
        '<option value="">Select City</option>';

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

    citySelect.innerHTML =
        '<option value="">Select City</option>';

    if (state === "") {
        citySelect.disabled = true;
        return;
    }

    citySelect.disabled = false;

    const cities = locationData[country][state];

    cities.forEach(function (city) {
        const option = document.createElement("option");

        option.value = city;
        option.textContent = city;

        citySelect.appendChild(option);
    });
});