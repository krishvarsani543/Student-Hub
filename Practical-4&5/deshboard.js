const successPopup = document.getElementById("successPopup");

const params = new URLSearchParams(window.location.search);

if (params.get("login") === "success" && successPopup) {
    successPopup.style.display = "block";

    setTimeout(function () {
        successPopup.style.display = "none";
    }, 5000);
}

// Automatically update logged-in student's name on dashboard
function updateDashboardUserInfo() {
    fetch("../Practical-7&8&9/get_current_user.php")
        .then(function (response) {
            if (!response.ok) {
                throw new Error("Failed to fetch user session");
            }
            return response.json();
        })
        .then(function (data) {
            if (data.loggedIn && data.name) {
                const userSpans = document.querySelectorAll(".user span, #topbarUserName");
                userSpans.forEach(function (el) {
                    el.textContent = data.name;
                });

                const welcomeHeading = document.getElementById("welcomeHeading") || document.querySelector(".welcome h1");
                if (welcomeHeading) {
                    welcomeHeading.textContent = "Welcome Back " + data.name + " 👋";
                }

                const welcomeCourse = document.getElementById("welcomeCourse") || document.querySelector(".welcome p");
                if (welcomeCourse && data.course) {
                    welcomeCourse.textContent = data.course;
                }
            }
        })
        .catch(function () {});
}

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", updateDashboardUserInfo);
} else {
    updateDashboardUserInfo();
}

const themeToggle = document.getElementById("themeToggle");

if (localStorage.getItem("theme") === "dark") {
    document.body.classList.add("dark-mode");
    themeToggle.textContent = "☀️ Light Mode";
} else {
    themeToggle.textContent = "🌙 Dark Mode";
}

themeToggle.addEventListener("click", function () {
    document.body.classList.toggle("dark-mode");

    if (document.body.classList.contains("dark-mode")) {
        themeToggle.textContent = "☀️ Light Mode";
        localStorage.setItem("theme", "dark");
    } else {
        themeToggle.textContent = "🌙 Dark Mode";
        localStorage.setItem("theme", "light");
    }
});

const weatherButton = document.getElementById("weatherButton");
const weatherPopup = document.getElementById("weatherPopup");
const closeWeather = document.getElementById("closeWeather");
const weatherInfo = document.getElementById("weatherInfo");

let weatherMap = null;
let weatherMarker = null;

weatherButton.addEventListener("click", function () {
    weatherPopup.style.display = "block";

    weatherInfo.innerHTML =
        "<p>📍 Getting your current location...</p>";

    if (!navigator.geolocation) {
        weatherInfo.innerHTML =
            "<p>❌ Geolocation is not supported by your browser.</p>";
        return;
    }

    navigator.geolocation.getCurrentPosition(
        function (position) {
            const latitude = position.coords.latitude;
            const longitude = position.coords.longitude;

            weatherInfo.innerHTML =
                "<p>🌦️ Getting your current weather...</p>";

            showMap(latitude, longitude);

            getLocationName(latitude, longitude)
                .then(function (location) {
                    getWeather(latitude, longitude, location);
                })
                .catch(function () {
                    getWeather(
                        latitude,
                        longitude,
                        {
                            city: "Current Location",
                            state: "",
                            country: ""
                        }
                    );
                });
        },
        function (error) {
            if (error.code === 1) {
                weatherInfo.innerHTML =
                    "<p>❌ Location permission was denied.</p><p>Please allow location access and try again.</p>";
            } else {
                weatherInfo.innerHTML =
                    "<p>❌ Unable to get your current location.</p>";
            }
        },
        {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0
        }
    );
});

closeWeather.addEventListener("click", function () {
    weatherPopup.style.display = "none";
});

weatherPopup.addEventListener("click", function (event) {
    if (event.target === weatherPopup) {
        weatherPopup.style.display = "none";
    }
});

function getLocationName(latitude, longitude) {
    const url =
        "https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=" +
        latitude +
        "&lon=" +
        longitude +
        "&zoom=18&addressdetails=1";

    return fetch(url)
        .then(function (response) {
            if (!response.ok) {
                throw new Error("Location lookup failed.");
            }

            return response.json();
        })
        .then(function (data) {
            const address = data.address || {};

            return {
                city:
                    address.city ||
                    address.town ||
                    address.village ||
                    address.municipality ||
                    address.county ||
                    "Current Location",

                state: address.state || "",

                country: address.country || ""
            };
        });
}

function getWeather(latitude, longitude, location) {
    const url =
        "https://api.open-meteo.com/v1/forecast?latitude=" +
        latitude +
        "&longitude=" +
        longitude +
        "&current=temperature_2m,relative_humidity_2m,apparent_temperature,weather_code,wind_speed_10m&temperature_unit=celsius&wind_speed_unit=kmh&timezone=auto";

    fetch(url)
        .then(function (response) {
            if (!response.ok) {
                throw new Error("Weather data could not be loaded.");
            }

            return response.json();
        })
        .then(function (data) {
            const current = data.current;
            const weather = getWeatherDetails(current.weather_code);

            let locationText = location.city;

            if (location.state !== "") {
                locationText += ", " + location.state;
            }

            if (location.country !== "") {
                locationText += ", " + location.country;
            }

            weatherInfo.innerHTML = `
                <p id="weatherLocation">📍 ${locationText}</p>

                <div id="weatherIcon">${weather.icon}</div>

                <p id="weatherTemperature">${current.temperature_2m}°C</p>

                <p><strong>${weather.condition}</strong></p>

                <p class="weather-detail">
                    🌡️ Feels like: ${current.apparent_temperature}°C
                </p>

                <p class="weather-detail">
                    💧 Humidity: ${current.relative_humidity_2m}%
                </p>

                <p class="weather-detail">
                    💨 Wind Speed: ${current.wind_speed_10m} km/h
                </p>
            `;

            if (weatherMap) {
                setTimeout(function () {
                    weatherMap.invalidateSize();
                }, 100);
            }
        })
        .catch(function () {
            weatherInfo.innerHTML =
                "<p>❌ Unable to load weather information.</p>";
        });
}

function showMap(latitude, longitude) {
    if (weatherMap) {
        weatherMap.remove();
        weatherMap = null;
        weatherMarker = null;
    }

    weatherMap = L.map("weatherMap").setView(
        [latitude, longitude],
        14
    );

    L.tileLayer(
        "https://tile.openstreetmap.org/{z}/{x}/{y}.png",
        {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }
    ).addTo(weatherMap);

    weatherMarker = L.marker([
        latitude,
        longitude
    ]).addTo(weatherMap);

    weatherMarker
        .bindPopup("📍 Your Current Location")
        .openPopup();
}

function getWeatherDetails(code) {
    if (code === 0) {
        return {
            icon: "☀️",
            condition: "Clear Sky"
        };
    }

    if (code === 1) {
        return {
            icon: "🌤️",
            condition: "Mainly Clear"
        };
    }

    if (code === 2) {
        return {
            icon: "⛅",
            condition: "Partly Cloudy"
        };
    }

    if (code === 3) {
        return {
            icon: "☁️",
            condition: "Overcast"
        };
    }

    if (code === 45 || code === 48) {
        return {
            icon: "🌫️",
            condition: "Fog"
        };
    }

    if (code >= 51 && code <= 57) {
        return {
            icon: "🌦️",
            condition: "Drizzle"
        };
    }

    if (code >= 61 && code <= 67) {
        return {
            icon: "🌧️",
            condition: "Rain"
        };
    }

    if (code >= 71 && code <= 77) {
        return {
            icon: "❄️",
            condition: "Snow"
        };
    }

    if (code >= 80 && code <= 82) {
        return {
            icon: "🌧️",
            condition: "Rain Showers"
        };
    }

    if (code === 85 || code === 86) {
        return {
            icon: "🌨️",
            condition: "Snow Showers"
        };
    }

    if (code >= 95 && code <= 99) {
        return {
            icon: "⛈️",
            condition: "Thunderstorm"
        };
    }

    return {
        icon: "🌦️",
        condition: "Unknown Weather"
    };
}