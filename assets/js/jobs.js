document.addEventListener("DOMContentLoaded", function () {

    const navbar = document.querySelector(".jobs-navbar");


    if (navbar) {

        window.addEventListener("scroll", function () {

            if (window.scrollY > 50) {

                navbar.classList.add("scrolled");

            } else {

                navbar.classList.remove("scrolled");

            }

        });

    }



    const cards = document.querySelectorAll(".job-card");


    cards.forEach(function (card) {

        card.classList.add("fade-up");

    });



    const observer = new IntersectionObserver(

        function (entries) {

            entries.forEach(function (entry) {

                if (entry.isIntersecting) {

                    entry.target.classList.add("show");

                }

            });

        },

        {
            threshold: 0.2
        }

    );



    cards.forEach(function (card) {

        observer.observe(card);

    });



    const searchInput = document.getElementById("jobSearch");

    const noJobs = document.getElementById("noJobsFound");

    const clearBtn = document.getElementById("clearSearch");



    if (searchInput) {


        searchInput.addEventListener("keyup", function () {


            const value = this.value
                .toLowerCase()
                .trim();


            let found = false;



            cards.forEach(function (card) {


                const data = card.dataset.search;


                if (data.includes(value)) {


                    card.style.display = "block";

                    found = true;


                } else {


                    card.style.display = "none";


                }


            });


            if (noJobs) {


                if (found) {

                    noJobs.style.display = "none";

                } else {

                    noJobs.style.display = "block";

                }


            }



        });


    }



    if (clearBtn) {


        clearBtn.addEventListener("click", function () {


            searchInput.value = "";



            cards.forEach(function (card) {


                card.style.display = "block";


            });



            if (noJobs) {

                noJobs.style.display = "none";

            }


        });


    }



    const buttons = document.querySelectorAll(

        ".jobs-login-btn, .jobs-view-btn, .jobs-apply-btn"

    );



    buttons.forEach(function (button) {


        button.addEventListener("click", function (e) {


            const ripple = document.createElement("span");


            const rect = this.getBoundingClientRect();



            const size = Math.max(

                rect.width,

                rect.height

            );



            ripple.style.width = size + "px";

            ripple.style.height = size + "px";



            ripple.style.left =

                e.clientX - rect.left - size / 2 + "px";



            ripple.style.top =

                e.clientY - rect.top - size / 2 + "px";



            ripple.classList.add("ripple");



            this.appendChild(ripple);



            setTimeout(function () {

                ripple.remove();

            }, 600);



        });


    });



    cards.forEach(function (card) {


        card.addEventListener("mousemove", function (e) {


            const rect = this.getBoundingClientRect();



            const x = e.clientX - rect.left;

            const y = e.clientY - rect.top;



            this.style.setProperty(

                "--x",

                x + "px"

            );



            this.style.setProperty(

                "--y",

                y + "px"

            );


        });


    });



    document.querySelectorAll(

        'a[href^="#"]'

    )

    .forEach(function (anchor) {


        anchor.addEventListener(

            "click",

            function (e) {


                const target = document.querySelector(

                    this.getAttribute("href")

                );



                if (target) {


                    e.preventDefault();



                    target.scrollIntoView({

                        behavior: "smooth"

                    });


                }


            }

        );


    });



});