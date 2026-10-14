
let allFAQs = [];



const faqContainer =
    document.getElementById("faqContainer");

const faqSearch =
    document.getElementById("faqSearch");

const faqLoading =
    document.getElementById("faqLoading");

const faqError =
    document.getElementById("faqError");



fetch("../Practical-6/faqs.json")

    .then(response => {



        if (!response.ok) {

            throw new Error(
                "Unable to load faqs.json"
            );

        }

        return response.json();

    })

    .then(data => {

        console.log("FAQs loaded:", data);

        allFAQs = data;



        faqLoading.style.display = "none";


        displayFAQs(allFAQs);

    })

    .catch(error => {

        console.error("FAQ Fetch Error:", error);

        faqLoading.style.display = "none";

        faqError.textContent =
            "Unable to load FAQs. Please try again.";

    });



faqSearch.addEventListener(
    "input",
    function () {

        const searchText =
            faqSearch.value
                .toLowerCase()
                .trim();


        const filteredFAQs =
            allFAQs.filter(faq => {

                return (

                    faq.question
                        .toLowerCase()
                        .includes(searchText)

                    ||

                    faq.answer
                        .toLowerCase()
                        .includes(searchText)

                );

            });


        console.log(
            "Filtered FAQs:",
            filteredFAQs
        );


        displayFAQs(filteredFAQs);

    }
);



function displayFAQs(faqs) {

    faqContainer.innerHTML = "";



    if (faqs.length === 0) {

        faqContainer.innerHTML = `

            <div class="no-faq">

                No matching FAQ found.

            </div>

        `;

        return;

    }




    faqs.forEach((faq, index) => {


        const article =
            document.createElement("article");

        article.className = "faq";


        const button =
            document.createElement("button");

        button.className = "question";


        button.textContent =
            `${index + 1}. ${faq.question}`;


        const answer =
            document.createElement("p");

        answer.className = "answer";


        answer.innerHTML =
            faq.answer;



        button.addEventListener(
            "click",
            function () {

                if (
                    answer.style.display === "block"
                ) {

                    answer.style.display = "none";

                }

                else {

                    answer.style.display = "block";

                }

            }
        );



        article.appendChild(button);

        article.appendChild(answer);



        faqContainer.appendChild(article);

    });

}