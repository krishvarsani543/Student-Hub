

let allStudents = [];



let currentPage = 1;




const studentsPerPage = 5;




const studentSearch =
    document.getElementById("studentSearch");

const departmentFilter =
    document.getElementById("departmentFilter");

const studentSort =
    document.getElementById("studentSort");

const studentContainer =
    document.getElementById("studentContainer");

const studentLoading =
    document.getElementById("studentLoading");

const studentError =
    document.getElementById("studentError");

const pagination =
    document.getElementById("pagination");




fetch("../Practical-6/admin.json")

    .then(response => {



        if (!response.ok) {

            throw new Error(
                "Unable to load students.json"
            );

        }

        return response.json();

    })

    .then(data => {

        console.log(
            "Students loaded successfully:",
            data
        );


        allStudents = data;



        studentLoading.style.display = "none";


     

        createDepartmentOptions();


        applyStudentFilters();

    })

    .catch(error => {

        console.error(
            "Student Fetch Error:",
            error
        );


        studentLoading.style.display = "none";


        studentError.textContent =
            "Unable to load student data. Please try again.";

    });


function createDepartmentOptions() {

    const departments =
        [...new Set(
            allStudents.map(
                student => student.department
            )
        )];


    departments.forEach(department => {

        const option =
            document.createElement("option");


        option.value = department;

        option.textContent = department;


        departmentFilter.appendChild(option);

    });

}



studentSearch.addEventListener(
    "input",
    function () {

        currentPage = 1;

        applyStudentFilters();

    }
);



departmentFilter.addEventListener(
    "change",
    function () {

        currentPage = 1;

        applyStudentFilters();

    }
);



studentSort.addEventListener(
    "change",
    function () {

        currentPage = 1;

        applyStudentFilters();

    }
);


function applyStudentFilters() {

    let filteredStudents =
        [...allStudents];



    const searchText =
        studentSearch.value
            .toLowerCase()
            .trim();


    if (searchText !== "") {

        filteredStudents =
            filteredStudents.filter(student => {

                return (

                    student.name
                        .toLowerCase()
                        .includes(searchText)

                    ||

                    student.id
                        .toLowerCase()
                        .includes(searchText)

                );

            });

    }


    const selectedDepartment =
        departmentFilter.value;


    if (
        selectedDepartment !== "all"
    ) {

        filteredStudents =
            filteredStudents.filter(student => {

                return (
                    student.department ===
                    selectedDepartment
                );

            });

    }



    const sortValue =
        studentSort.value;


    if (sortValue === "nameAsc") {

        filteredStudents.sort(
            (a, b) =>
                a.name.localeCompare(b.name)
        );

    }


    else if (sortValue === "nameDesc") {

        filteredStudents.sort(
            (a, b) =>
                b.name.localeCompare(a.name)
        );

    }


    else if (sortValue === "idAsc") {

        filteredStudents.sort(
            (a, b) =>
                a.id.localeCompare(b.id)
        );

    }


    else if (sortValue === "idDesc") {

        filteredStudents.sort(
            (a, b) =>
                b.id.localeCompare(a.id)
        );

    }


    console.log(
        "Filtered students:",
        filteredStudents
    );



    displayStudents(filteredStudents);

}



function displayStudents(students) {

    studentContainer.innerHTML = "";

    pagination.innerHTML = "";




    if (students.length === 0) {

        studentContainer.innerHTML = `

            <div class="no-student">

                No matching student found.

            </div>

        `;

        return;

    }



    const totalPages =
        Math.ceil(
            students.length /
            studentsPerPage
        );


    if (currentPage > totalPages) {

        currentPage = totalPages;

    }


    const startIndex =
        (currentPage - 1) *
        studentsPerPage;


    const endIndex =
        startIndex +
        studentsPerPage;


    const currentStudents =
        students.slice(
            startIndex,
            endIndex
        );



    currentStudents.forEach(student => {

        const card =
            document.createElement("div");


        card.className =
            "student-card";


        card.innerHTML = `

            <h3>${student.name}</h3>

            <p>
                <strong>Student ID:</strong>
                ${student.id}
            </p>

            <p>
                <strong>Department:</strong>
                ${student.department}
            </p>

            <p>
                <strong>Semester:</strong>
                ${student.semester}
            </p>

            <p>
                <strong>Email:</strong>
                ${student.email}
            </p>

        `;


        studentContainer.appendChild(card);

    });


  

    createPagination(
        totalPages,
        students
    );

}


function createPagination(
    totalPages,
    students
) {

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
        function () {

            if (currentPage > 1) {

                currentPage--;

                displayStudents(students);

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
            function () {

                currentPage = page;

                displayStudents(students);

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
        function () {

            if (
                currentPage < totalPages
            ) {

                currentPage++;

                displayStudents(students);

            }

        }
    );


    pagination.appendChild(
        nextButton
    );

}