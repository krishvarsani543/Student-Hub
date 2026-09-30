const successPopup = document.getElementById("successPopup");

const params = new URLSearchParams(window.location.search);

if (params.get("login") === "success") {

    successPopup.style.display = "block";

    setTimeout(function () {

        successPopup.style.display = "none";

    }, 5000);

}


const themeToggle = document.getElementById("themeToggle");



if (localStorage.getItem("theme") === "dark") {

    document.body.classList.add("dark-mode");

    themeToggle.textContent = "☀️ Light Mode";
}



themeToggle.addEventListener("click", function () {

    document.body.classList.toggle("dark-mode");


    
    if (document.body.classList.contains("dark-mode")) {

        themeToggle.textContent = "☀️ Light Mode";

        localStorage.setItem("theme", "dark");

    }

   
    else {

        themeToggle.textContent = "🌙 Dark Mode";

        localStorage.setItem("theme", "light");
    }

});