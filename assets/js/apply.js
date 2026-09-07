document.addEventListener("DOMContentLoaded", function () {


    /* ==========================================================
       GET FORM ELEMENTS
    ========================================================== */


    const form = document.querySelector(
        ".apply-card form"
    );


    if (!form) return;



    const phone =
        form.querySelector(
            'input[name="phone"]'
        );


    const salary =
        form.querySelector(
            'input[name="expected_salary"]'
        );


    const birthdate =
        form.querySelector(
            'input[name="birthdate"]'
        );


    const availableDate =
        form.querySelector(
            'input[name="available_date"]'
        );


    const resume =
        form.querySelector(
            'input[name="resume"]'
        );


    const coverLetter =
        form.querySelector(
            'input[name="cover_letter"]'
        );


    const submitButton =
        form.querySelector(
            ".apply-submit-btn"
        );





    /* ==========================================================
       DATE SETTINGS
    ========================================================== */


    const today =
        new Date()
        .toISOString()
        .split("T")[0];



    if (availableDate) {

        availableDate.min = today;

    }



    if (birthdate) {

        birthdate.max = today;

    }







    /* ==========================================================
       PHONE VALIDATION INPUT
    ========================================================== */


    if (phone) {


        phone.addEventListener(
            "input",
            function () {


                this.value =
                this.value.replace(
                    /\D/g,
                    ""
                );



                if(this.value.length > 11){


                    this.value =
                    this.value.substring(
                        0,
                        11
                    );


                }



            }
        );


    }







    /* ==========================================================
       SALARY ONLY NUMBERS
    ========================================================== */


    if (salary) {


        salary.addEventListener(
            "input",
            function(){


                this.value =
                this.value.replace(
                    /\D/g,
                    ""
                );


            }
        );


    }








    /* ==========================================================
       DATE INPUT CLEAN
    ========================================================== */


    document
    .querySelectorAll(
        'input[type="date"]'
    )
    .forEach(function(input){



        input.addEventListener(
            "change",
            function(){



                if(
                    this.value < "1900-01-01"
                ){

                    this.value = "";

                }



            }
        );



    });








    /* ==========================================================
       FORM SUBMIT VALIDATION
    ========================================================== */


    form.addEventListener(
        "submit",
        function(e){



            removeError();



            let errors = [];







            /* ==============================
               AGE CHECK
            ============================== */


            if(
                birthdate &&
                birthdate.value
            ){


                const birth =
                new Date(
                    birthdate.value
                );


                const current =
                new Date();



                let age =
                current.getFullYear()
                -
                birth.getFullYear();



                const month =
                current.getMonth()
                -
                birth.getMonth();




                if(
                    month < 0
                    ||
                    (
                        month === 0
                        &&
                        current.getDate()
                        <
                        birth.getDate()
                    )
                ){

                    age--;

                }





                if(age < 18){


                    errors.push(
                        "Applicant must be at least 18 years old."
                    );


                }



            }









            /* ==============================
               AVAILABLE DATE CHECK
            ============================== */


            if(
                availableDate &&
                availableDate.value
            ){



                if(
                    availableDate.value
                    <
                    today
                ){



                    errors.push(
                        "Available start date cannot be earlier than today."
                    );



                }


            }










            /* ==============================
               PHONE CHECK
            ============================== */


            if(
                phone &&
                phone.value
            ){



                if(
                    !/^09\d{9}$/
                    .test(
                        phone.value
                    )
                ){


                    errors.push(
                        "Phone number must start with 09 and contain exactly 11 digits."
                    );


                }



            }











            /* ==============================
               SALARY CHECK
            ============================== */


            if(
                salary &&
                salary.value
            ){



                if(
                    Number(
                        salary.value
                    )
                    <=0
                ){



                    errors.push(
                        "Expected salary must be greater than zero."
                    );



                }



            }









            /* ==============================
               RESUME CHECK
            ============================== */


            if(resume){



                if(
                    resume.files.length === 0
                ){


                    errors.push(
                        "Resume is required."
                    );


                }

                else{


                    const file =
                    resume.files[0];



                    const extension =
                    file.name
                    .split(".")
                    .pop()
                    .toLowerCase();




                    const allowed =
                    [
                        "pdf",
                        "doc",
                        "docx"
                    ];




                    if(
                        !allowed.includes(
                            extension
                        )
                    ){


                        errors.push(
                            "Resume must be PDF, DOC, or DOCX only."
                        );


                    }




                    if(
                        file.size
                        >
                        5 *
                        1024 *
                        1024
                    ){


                        errors.push(
                            "Resume file size must not exceed 5MB."
                        );


                    }



                }



            }










            /* ==============================
               COVER LETTER CHECK
               OPTIONAL
            ============================== */


            if(
                coverLetter &&
                coverLetter.files.length > 0
            ){


                const file =
                coverLetter.files[0];



                const extension =
                file.name
                .split(".")
                .pop()
                .toLowerCase();




                if(
                    ![
                        "pdf",
                        "doc",
                        "docx"
                    ]
                    .includes(
                        extension
                    )
                ){


                    errors.push(
                        "Cover letter must be PDF, DOC, or DOCX only."
                    );


                }



            }









            /* ==============================
               STOP SUBMIT IF ERROR
            ============================== */


            if(
                errors.length > 0
            ){


                e.preventDefault();


                showError(errors);


                if(submitButton){


                    submitButton.disabled =
                    false;


                }


                return;


            }









            /* ==============================
               LOADING STATE
            ============================== */


            if(submitButton){


                submitButton.disabled =
                true;



                submitButton.innerHTML =
                `

                <i class="fa-solid fa-spinner fa-spin"></i>

                Submitting Application...

                `;


            }




        }

    );









    /* ==========================================================
       SUCCESS MODAL AUTO SHOW
    ========================================================== */


    const successModal =
    document.querySelector(
        ".success-modal"
    );



    if(successModal){


        setTimeout(
            function(){


                successModal.classList.add(
                    "show"
                );


            },
            100
        );


    }









    /* ==========================================================
       ERROR DISPLAY FUNCTION
    ========================================================== */


    function showError(errors){



        const oldError =
        document.querySelector(
            ".apply-js-error"
        );



        if(oldError){

            oldError.remove();

        }





        const box =
        document.createElement(
            "div"
        );



        box.className =
        "apply-js-error";





        box.innerHTML = `

            <i class="fa-solid fa-circle-exclamation"></i>

            <div>

            ${
                [...new Set(errors)]
                .map(
                    error =>
                    `<p>${error}</p>`
                )
                .join("")
            }

            </div>

        `;





        form.prepend(box);





        box.scrollIntoView({

            behavior:"smooth",

            block:"center"

        });



    }









    /* ==========================================================
       REMOVE OLD ERROR
    ========================================================== */


    function removeError(){



        const oldError =
        document.querySelector(
            ".apply-js-error"
        );



        if(oldError){

            oldError.remove();

        }



    }





});