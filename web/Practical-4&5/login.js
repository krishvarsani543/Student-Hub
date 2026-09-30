const loginForm = document.getElementById("loginForm");

loginForm.addEventListener("submit", function (event) {

    event.preventDefault();

    const email = document.getElementById("email").value;

    const password = document.getElementById("password").value;

    
    const emailregex = /^[0-9]{2}d[a-zA-Z]{2}[0-9]{3}@charusat\.edu\.in$/;

   
    const passwordregex = /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)[A-Za-z\d]{8,}$/;


    if (!emailregex.test(email)) {

        document.getElementById("errormessage").textContent =
            "Please enter valid email";

        return;
    }


    if (!passwordregex.test(password)) {

        document.getElementById("errormessage").textContent =
            "Password must contain at least 8 char, 1 letter and 1 number";

        return;
    }


    document.getElementById("errormessage").textContent =
        "Login successful";


    
    window.location.href = "deshboard.html?login=success";

});





const urlParams = new URLSearchParams(window.location.search);

if (urlParams.get("registered") === "success") {

    const registrationModal =
        document.getElementById("registrationModal");

    registrationModal.style.display = "flex";
}




const closeRegistrationModal =
    document.getElementById("closeRegistrationModal");

const okRegistrationModal =
    document.getElementById("okRegistrationModal");

const registrationModal =
    document.getElementById("registrationModal");



closeRegistrationModal.addEventListener("click", function () {

    registrationModal.style.display = "none";

});


okRegistrationModal.addEventListener("click", function () {

    registrationModal.style.display = "none";

});
