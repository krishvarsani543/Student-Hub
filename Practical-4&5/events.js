
let allEvents = [];

let currentEvents = [];

let currentPage = 1;

const eventsPerPage = 4;




const eventsContainer =
    document.getElementById("eventsContainer");

const searchInput =
    document.getElementById("searchInput");

const categoryFilter =
    document.getElementById("categoryFilter");

const sortSelect =
    document.getElementById("sortSelect");

const loadingMessage =
    document.getElementById("loadingMessage");

const errorMessage =
    document.getElementById("errorMessage");

const pagination =
    document.getElementById("pagination");



fetch("../Practical-6/events.json")

    .then(response => {

       

        if (!response.ok) {

            throw new Error(
                "Unable to load events.json"
            );

        }

        return response.json();

    })

    .then(data => {

        console.log("Events loaded:", data);

        allEvents = data;

        currentEvents = [...allEvents];




        loadingMessage.style.display = "none";



        createCategoryOptions();



        applyFilters();

    })

    .catch(error => {

        console.error("Fetch Error:", error);

        loadingMessage.style.display = "none";

        errorMessage.textContent =
            "Unable to load events. Please try again.";

    });


function createCategoryOptions() {

    const categories = [
        ...new Set(
            allEvents.map(event => event.category)
        )
    ];


    categories.forEach(category => {

        const option =
            document.createElement("option");

        option.value = category;

        option.textContent = category;

        categoryFilter.appendChild(option);

    });

}



searchInput.addEventListener(
    "input",
    applyFilters
);



categoryFilter.addEventListener(
    "change",
    applyFilters
);



sortSelect.addEventListener(
    "change",
    applyFilters
);



function applyFilters() {

    const searchText =
        searchInput.value
            .toLowerCase()
            .trim();


    const selectedCategory =
        categoryFilter.value;


    let filteredEvents =
        allEvents.filter(event => {

            const matchesSearch =

                event.title
                    .toLowerCase()
                    .includes(searchText)

                ||

                event.description
                    .toLowerCase()
                    .includes(searchText)

                ||

                event.location
                    .toLowerCase()
                    .includes(searchText);


            const matchesCategory =

                selectedCategory === "all"

                ||

                event.category === selectedCategory;


            return (
                matchesSearch &&
                matchesCategory
            );

        });


    const sortValue =
        sortSelect.value;


    if (sortValue === "date-asc") {

        filteredEvents.sort(
            (a, b) =>
                new Date(a.date) -
                new Date(b.date)
        );

    }


    else if (sortValue === "date-desc") {

        filteredEvents.sort(
            (a, b) =>
                new Date(b.date) -
                new Date(a.date)
        );

    }


    else if (sortValue === "title-asc") {

        filteredEvents.sort(
            (a, b) =>
                a.title.localeCompare(b.title)
        );

    }


    else if (sortValue === "title-desc") {

        filteredEvents.sort(
            (a, b) =>
                b.title.localeCompare(a.title)
        );

    }


    currentEvents = filteredEvents;

    currentPage = 1;


    displayEvents();

}



function displayEvents() {

    eventsContainer.innerHTML = "";


    if (currentEvents.length === 0) {

        eventsContainer.innerHTML =
            "<p class='status-message'>No events found.</p>";

        pagination.innerHTML = "";

        return;

    }




    const start =
        (currentPage - 1) *
        eventsPerPage;


    const end =
        start + eventsPerPage;


    const pageEvents =
        currentEvents.slice(start, end);


   

    pageEvents.forEach(event => {

        const eventCard =
            document.createElement("div");

        eventCard.className =
            "event-card";


  

        const eventDate =
            new Date(event.date);


        const day =
            String(
                eventDate.getDate()
            ).padStart(2, "0");


        const month =
            eventDate
                .toLocaleString(
                    "en-US",
                    {
                        month: "short"
                    }
                )
                .toUpperCase();


        eventCard.innerHTML = `

            <div class="date">

                <h2>${day}</h2>

                <span>${month}</span>

            </div>


            <div class="details">

                <h3>${event.title}</h3>


                <p>
                    📍 ${event.location}
                </p>


                <p>
                    🕒 ${event.time}
                </p>


                <p>
                    ${event.description}
                </p>


                <p>
                    <b>Category:</b>
                    ${event.category}
                </p>


                <a href="#">
                    ${event.buttonText}
                </a>

            </div>

        `;


        eventsContainer.appendChild(
            eventCard
        );

    });


    createPagination();

}

function createPagination() {

    pagination.innerHTML = "";


    const totalPages =
        Math.ceil(
            currentEvents.length /
            eventsPerPage
        );


    if (totalPages <= 1) {

        return;

    }


    const previousButton =
        document.createElement("button");

    previousButton.textContent =
        "Previous";

    previousButton.disabled =
        currentPage === 1;


    previousButton.addEventListener(
        "click",
        () => {

            if (currentPage > 1) {

                currentPage--;

                displayEvents();

                window.scrollTo(
                    {
                        top: 0,
                        behavior: "smooth"
                    }
                );

            }

        }
    );


    pagination.appendChild(
        previousButton
    );


    

    for (
        let page = 1;
        page <= totalPages;
        page++
    ) {

        const pageButton =
            document.createElement("button");


        pageButton.textContent =
            page;


        if (page === currentPage) {

            pageButton.classList.add(
                "active"
            );

        }


        pageButton.addEventListener(
            "click",
            () => {

                currentPage = page;

                displayEvents();

                window.scrollTo(
                    {
                        top: 0,
                        behavior: "smooth"
                    }
                );

            }
        );


        pagination.appendChild(
            pageButton
        );

    }


   

    const nextButton =
        document.createElement("button");

    nextButton.textContent =
        "Next";


    nextButton.disabled =
        currentPage === totalPages;


    nextButton.addEventListener(
        "click",
        () => {

            if (
                currentPage <
                totalPages
            ) {

                currentPage++;

                displayEvents();

                window.scrollTo(
                    {
                        top: 0,
                        behavior: "smooth"
                    }
                );

            }

        }
    );


    pagination.appendChild(
        nextButton
    );

}