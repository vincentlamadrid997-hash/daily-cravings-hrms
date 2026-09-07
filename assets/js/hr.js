/*
==========================================================
        JOB POSTING REOPEN / CLOSE CONFIRMATION
==========================================================
*/
document.querySelectorAll(".hr-job-status-form").forEach(function (form) {

    form.addEventListener("submit", function (e) {

        e.preventDefault();

        const button = form.querySelector("button");
        const dateField = form.querySelector('input[name="new_closing_date"]');
        const isClosed = button.classList.contains("closed");

        if (isClosed) {

            // STEP 1: pick the new closing date
            Swal.fire({

                title: "Set New Closing Date",
                text: "Pick when this job posting should close.",
                icon: "question",
                input: "date",
                inputAttributes: {
                    min: new Date().toISOString().split("T")[0]
                },
                showCancelButton: true,
                confirmButtonColor: "#003DA5",
                cancelButtonColor: "#90A4AE",
                confirmButtonText: "Next",
                inputValidator: (value) => {
                    if (!value) {
                        return "Please select a date.";
                    }
                }

            }).then(function (dateResult) {

                if (!dateResult.isConfirmed) {
                    return;
                }

                const chosenDate = dateResult.value;

                // STEP 2: confirm before actually submitting
                Swal.fire({

                    title: "Reopen this job posting?",
                    text: "It will be marked Open with a closing date of " + chosenDate + ".",
                    icon: "question",
                    showCancelButton: true,
                    confirmButtonColor: "#2E7D32",
                    cancelButtonColor: "#90A4AE",
                    confirmButtonText: "Yes, Reopen"

                }).then(function (confirmResult) {

                    if (confirmResult.isConfirmed) {

                        dateField.value = chosenDate;
                        form.submit();

                    }

                });

            });

        } else {

            // Closing needs no date — just confirm
            Swal.fire({

                title: "Close this job posting?",
                text: "Applicants will no longer be able to apply.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#C62828",
                cancelButtonColor: "#90A4AE",
                confirmButtonText: "Yes, Close"

            }).then(function (result) {

                if (result.isConfirmed) {
                    form.submit();
                }

            });

        }

    });

});


/*
==========================================================
                    HR GLOBAL FUNCTIONS
==========================================================
*/

document.addEventListener("DOMContentLoaded", () => {

    "use strict";

    const sidebar = document.querySelector(".hr-sidebar");
    const sidebarToggle = document.querySelector(".hr-sidebar-toggle");
    const sidebarOverlay = document.querySelector(".hr-sidebar-overlay");
    const sidebarLinks = document.querySelectorAll(".hr-sidebar-link");
    const currentDate = document.getElementById("hrCurrentDate");










    /*
    ==========================================================
                        HR HEADER DATE
    ==========================================================
    */

    if (currentDate) {

        currentDate.textContent = new Date().toLocaleDateString(
            "en-US",
            {
                weekday: "long",
                year: "numeric",
                month: "long",
                day: "numeric"
            }
        );

    }










    /*
    ==========================================================
                        SIDEBAR FUNCTIONS
    ==========================================================
    */

    function closeSidebar() {

        sidebar?.classList.remove("active");
        sidebarOverlay?.classList.remove("show");

    }

    function toggleSidebar() {

        sidebar?.classList.toggle("active");
        sidebarOverlay?.classList.toggle("show");

    }

    sidebarToggle?.addEventListener(
        "click",
        toggleSidebar
    );

    sidebarOverlay?.addEventListener(
        "click",
        closeSidebar
    );

    sidebarLinks.forEach(link => {

        link.addEventListener(
            "click",
            () => {

                if (window.innerWidth <= 992) {

                    closeSidebar();

                }

            }
        );

    });










    /*
    ==========================================================
                    SIDEBAR KEYBOARD CONTROL
    ==========================================================
    */

    document.addEventListener(
        "keydown",
        event => {

            if (event.key === "Escape") {

                closeSidebar();

            }

        }
    );










    /*
    ==========================================================
                    WINDOW RESIZE CONTROL
    ==========================================================
    */

    window.addEventListener(
        "resize",
        () => {

            if (window.innerWidth > 992) {

                closeSidebar();

            }

        }
    );










    /*
    ==========================================================
                    ACTIVE SIDEBAR LINK
    ==========================================================
    */

    const currentPage = window.location.pathname
        .split("/")
        .pop();

    sidebarLinks.forEach(link => {

        if (link.getAttribute("href") === currentPage) {

            link.classList.add("active");

        }

    });










    /*
    ==========================================================
                    DASHBOARD COUNTER ANIMATION
    ==========================================================
    */

    document.querySelectorAll(
        ".hr-dashboard-value, .hr-stat-content h2"
    )
    .forEach(counter => {

        const target = parseInt(
            counter.textContent.replace(/,/g, "")
        );

        if (isNaN(target)) return;

        let current = 0;

        const increment = Math.max(
            1,
            Math.ceil(target / 50)
        );

        function animate() {

            current += increment;

            if (current >= target) {

                counter.textContent =
                    target.toLocaleString();

                return;

            }

            counter.textContent =
                current.toLocaleString();

            requestAnimationFrame(animate);

        }

        animate();

    });










    /*
    ==========================================================
                    DASHBOARD CARD ANIMATION
    ==========================================================
    */

    document.querySelectorAll(
        ".hr-dashboard-card, .hr-stat-card, .hr-widget, .hr-chart-card, .hr-quick-card"
    )
    .forEach((item, index) => {

        item.style.animationDelay =
            `${index * 0.08}s`;

    });

});











/*
==========================================================
                    BUTTON CLICK EFFECT
==========================================================
*/

document.querySelectorAll(
    "button, .hr-sidebar-link, .hr-quick-card"
)
.forEach(button => {

    button.addEventListener(
        "click",
        () => {

            button.classList.add("clicked");

            setTimeout(
                () => {

                    button.classList.remove("clicked");

                },
                250
            );

        }
    );

});










/*
==========================================================
                    JOB POSTINGS 
==========================================================
*/

document.addEventListener(
"DOMContentLoaded",
function(){

const alertBox =
document.getElementById(
    "jobAlertData"
);

if(alertBox){

    const success =
    alertBox.dataset.success;

    const error =
    alertBox.dataset.error;

    if(success){

        Swal.fire({

            icon:"success",
            title:"Success!",
            text:success,
            confirmButtonColor:"#003DA5"

        });

    }

    if(error){

        Swal.fire({

            icon:"error",
            title:"Unable to Continue",
            text:error,
            confirmButtonColor:"#003DA5"

        });

    }

}



const search =
document.getElementById(
    "jobSearch"
);

const rows =
document.querySelectorAll(
    ".job-row"
);

const noResult =
document.getElementById(
    "noJobResult"
);

if(search){

    search.addEventListener(
    "input",
    function(){

        let keyword =
        this.value
        .toLowerCase()
        .trim();

        let visible = 0;

        rows.forEach(
        function(row){

            let text =
            row.textContent
            .toLowerCase();

            if(
                text.includes(keyword)
            ){

                row.style.display = "";
                visible++;

            }
            else{

                row.style.display = "none";

            }

        });

        if(noResult){
            noResult.style.display =
            visible === 0
            ? ""
            : "none";

        }

    });

}



const form =
document.getElementById(
    "jobForm"
);

if(form){

    const fields =
    form.querySelectorAll(
        ".hr-job-group input, .hr-job-group select, .hr-job-group textarea"
    );

    fields.forEach(
    function(field){

        field.addEventListener(
        "input",
        function(){

            removeError(this);

        });

        field.addEventListener(
        "change",
        function(){

            removeError(this);

        });

    });

    function removeError(field){

        field.classList.remove(
            "input-error"
        );

        const group =
        field.closest(
            ".hr-job-group"
        );

        if(group){

            const warning =
            group.querySelector(
                ".hr-job-warning"
            );

            if(warning){

                warning.remove();

            }

        }

    }



    form.addEventListener(
    "submit",
    function(e){

        let hasError = false;

        fields.forEach(
        function(field){

            if(
                field.value.trim() === "" ||
                field.value === null
            ){

                hasError = true;

                field.classList.add(
                    "input-error"
                );

                const group =
                field.closest(
                    ".hr-job-group"
                );

                if(
                    group &&
                    !group.querySelector(
                        ".hr-job-warning"
                    )
                ){

                    const warning =
                    document.createElement(
                        "span"
                    );

                    warning.className =
                    "hr-job-warning";

                    warning.innerHTML =
                    "This field is required.";

                    group.appendChild(
                        warning
                    );

                }

            }

        });

        if(hasError){

            e.preventDefault();

            return;

        }

        const button =
        this.querySelector(
            "button[type='submit']"
        );

        if(button){

            button.disabled = true;

            button.innerHTML = `
                <i class="fa-solid fa-spinner fa-spin"></i>
                Saving...
            `;

        }

    });

}



const postedDate =
document.querySelector(
    'input[name="posted_date"]'
);

const closingDate =
document.querySelector(
    'input[name="closing_date"]'
);

if(
    postedDate &&
    closingDate
){

    closingDate.addEventListener(
    "change",
    function(){

        if(
            this.value &&
            postedDate.value &&
            this.value < postedDate.value
        ){

            Swal.fire({

                icon:"warning",
                title:"Invalid Date",
                text:"Closing date cannot be earlier than posted date.",
                confirmButtonColor:"#003DA5"

            });

            this.value = "";

        }

    });

}



const salary =
document.getElementById(
    "salary"
);

if(salary){

    salary.addEventListener(
    "input",
    function(){

        this.value =
        this.value.replace(
            /[^0-9.]/g,
            ""
        );

    });

    salary.addEventListener(
    "blur",
    function(){

        if(this.value !== ""){

            this.value =
            Number(this.value)
            .toLocaleString(
                "en-US",
                {

                    minimumFractionDigits:2,
                    maximumFractionDigits:2

                }
            );

        }

    });

}

});

























































/*
==========================================================
                    INTERVIEW & ATTENDANCE HOVER EFFECT
==========================================================

USED BY:
- interviews.php
- attendance.php


TARGET CLASSES:

.hr-interview-item
.hr-attendance-card


FUNCTION:
- Adds hover lift animation effect


==========================================================
*/


[
    ".hr-interview-item",
    ".hr-attendance-card"
]
.forEach(selector => {


    document.querySelectorAll(selector)
    .forEach(item => {


        item.addEventListener(
            "mouseenter",
            () => {


                item.style.transform =
                    "translateY(-4px)";


            }
        );



        item.addEventListener(
            "mouseleave",
            () => {


                item.style.transform = "";


            }
        );



    });


});








/*
==========================================================
                    APPLICANTS SEARCH MODULE
==========================================================

USED BY:
- applicants.php


FUNCTION:
- Search applicant records


REQUIRED HTML:

SEARCH:
.hr-app-search-box input


TABLE:
.hr-app-table tbody


EMPTY RESULT:
.hr-app-empty


==========================================================
*/


const applicantSearch = document.querySelector(
    ".hr-app-search-box input"
);



const applicantTable = document.querySelector(
    ".hr-app-table tbody"
);



if (applicantSearch && applicantTable) {



    const rows = applicantTable.querySelectorAll("tr");



    const emptyRow = document.createElement("tr");



    emptyRow.innerHTML = `

        <td colspan="6">

            <div class="hr-app-empty">

                <i class="fa-solid fa-user-slash"></i>

                <h3>No Applicants Found</h3>

                <p>No records matched your search.</p>

            </div>

        </td>

    `;



    emptyRow.style.display = "none";



    applicantTable.appendChild(emptyRow);





    applicantSearch.addEventListener(
        "keyup",
        function () {



            const keyword = this.value
                .toLowerCase()
                .trim();



            let found = false;





            rows.forEach(row => {



                if (row === emptyRow) return;





                const text = row.textContent
                    .toLowerCase();





                if (text.includes(keyword)) {



                    row.style.display = "";

                    found = true;



                } else {



                    row.style.display = "none";



                }



            });





            emptyRow.style.display =

                (!found && keyword !== "")

                    ? "table-row"

                    : "none";



        }
    );



}









/*
==========================================================
                    INTERVIEW SEARCH MODULE
==========================================================

USED BY:
- interviews.php


FUNCTION:
- Search scheduled interviews


REQUIRED:

ID:
#interviewSearch


CLASS:
.interview-row


ID:
#noInterviewResult


==========================================================
*/


const interviewSearch = document.getElementById(
    "interviewSearch"
);



const interviewRows = document.querySelectorAll(
    ".interview-row"
);



const interviewEmpty = document.getElementById(
    "noInterviewResult"
);





if (interviewSearch) {



    interviewSearch.addEventListener(
        "keyup",
        function () {



            const keyword = this.value
                .toLowerCase()
                .trim();





            let count = 0;





            interviewRows.forEach(row => {



                const text = row.textContent
                    .toLowerCase();





                if (text.includes(keyword)) {



                    row.style.display = "";

                    count++;




                } else {



                    row.style.display = "none";



                }



            });





            if (interviewEmpty) {



                interviewEmpty.style.display =

                    count === 0

                        ? ""

                        : "none";



            }



        }
    );



}









/*
==========================================================
                    HIRE APPLICANT CONFIRMATION
==========================================================

USED BY:
- applicants.php


FUNCTION:
- Prevent hiring applicants
  who are not yet Accepted


PROCESS:

1. Check applicant status
2. Show warning if not Accepted
3. Show confirmation before hiring


REQUIRED:
- SweetAlert2 CDN


==========================================================
*/



function hireConfirm(form, status) {



    if (status !== "Accepted") {



        Swal.fire({


            icon: "warning",


            title: "Cannot Hire Applicant",


            text: "Applicant must be Accepted first before hiring.",


            confirmButtonColor: "#003DA5"



        });



        return false;



    }






    Swal.fire({



        title: "Hire this applicant?",



        text: "Employee record will be created.",



        icon: "question",



        showCancelButton: true,



        confirmButtonText: "Yes, Hire",



        cancelButtonText: "Cancel",



        confirmButtonColor: "#003DA5",



        cancelButtonColor: "#DC3545"



    })



    .then(result => {



        if (result.isConfirmed) {



            form.submit();



        }



    });





    return false;



}

/*
==========================================================
                    EMPLOYEE DEACTIVATE MODULE
==========================================================

USED BY:
- employees.php


FUNCTION:
- Confirmation before deactivating employee


REQUIRED CLASS:

.deactivate-form


PROCESS:

1. Prevent default submit
2. Show SweetAlert confirmation
3. Submit form if confirmed


REQUIRED:
- SweetAlert2 CDN


==========================================================
*/


document.querySelectorAll(".deactivate-form")
.forEach(form => {


    form.addEventListener(
        "submit",
        function (event) {


            event.preventDefault();



            Swal.fire({


                title: "Deactivate Employee?",


                text: "This employee account will be marked as inactive.",


                icon: "warning",


                showCancelButton: true,


                confirmButtonColor: "#003DA5",


                cancelButtonColor: "#999",


                confirmButtonText: "Yes, deactivate"



            })

            .then(result => {



                if (result.isConfirmed) {



                    form.submit();



                }



            });



        }
    );


});









/*
==========================================================
                    EMPLOYEE SEARCH MODULE
==========================================================

USED BY:
- employees.php


FUNCTION:
- Search employee records


REQUIRED:

ID:
#employeeSearch

#employeeTableBody


CLASS:

.employee-row


EMPTY RESULT:

#noEmployeeResult


==========================================================
*/


document.addEventListener(
    "DOMContentLoaded",
    () => {


        const employeeSearch = document.getElementById(
            "employeeSearch"
        );



        const employeeTableBody = document.getElementById(
            "employeeTableBody"
        );



        const employeeRows = document.querySelectorAll(
            ".employee-row"
        );



        const noEmployeeResult = document.getElementById(
            "noEmployeeResult"
        );




        if (!employeeSearch) return;





        employeeSearch.addEventListener(
            "keyup",
            function () {



                const keyword = this.value
                    .toLowerCase()
                    .trim();





                let found = false;





                employeeRows.forEach(row => {




                    const text = row.textContent
                        .toLowerCase();





                    if (text.includes(keyword)) {



                        row.style.display = "";

                        found = true;



                    } else {



                        row.style.display = "none";



                    }



                });





                if (noEmployeeResult) {



                    noEmployeeResult.style.display =

                        found

                            ? "none"

                            : "";



                }



            }
        );



    }
);









/*
==========================================================
                    DEPARTMENT SEARCH MODULE
==========================================================

USED BY:
- departments.php


FUNCTION:
- Search department records


REQUIRED:

ID:

#departmentSearch

#departmentTableBody


CLASS:

.department-row


EMPTY RESULT:

#noDepartmentResult


==========================================================
*/


document.addEventListener(
    "DOMContentLoaded",
    () => {


        const departmentSearch = document.getElementById(
            "departmentSearch"
        );



        const departmentTableBody = document.getElementById(
            "departmentTableBody"
        );





        if (!departmentSearch || !departmentTableBody) return;





        const departmentRows =

            departmentTableBody.querySelectorAll(
                ".department-row"
            );





        const noDepartmentResult = document.getElementById(
            "noDepartmentResult"
        );





        departmentSearch.addEventListener(
            "keyup",
            function () {




                const keyword = this.value
                    .toLowerCase()
                    .trim();





                let visibleCount = 0;





                departmentRows.forEach(row => {




                    const text = row.textContent
                        .toLowerCase();





                    if (text.includes(keyword)) {



                        row.style.display = "";

                        visibleCount++;




                    } else {



                        row.style.display = "none";



                    }




                });





                if (noDepartmentResult) {



                    noDepartmentResult.style.display =

                        visibleCount === 0

                            ? ""

                            : "none";



                }





            }
        );



    }
);









/*
==========================================================
                    POSITION SEARCH MODULE
==========================================================

USED BY:
- positions.php


FUNCTION:
- Search position records


REQUIRED:

ID:

#positionSearch

#positionTableBody


CLASS:

.position-row


EMPTY RESULT:

#noPositionResult


==========================================================
*/


document.addEventListener(
    "DOMContentLoaded",
    () => {


        const positionSearch = document.getElementById(
            "positionSearch"
        );



        const positionTableBody = document.getElementById(
            "positionTableBody"
        );





        if (!positionSearch || !positionTableBody) return;





        const positionRows =

            positionTableBody.querySelectorAll(
                ".position-row"
            );





        const noPositionResult = document.getElementById(
            "noPositionResult"
        );





        positionSearch.addEventListener(
            "keyup",
            function () {




                const keyword = this.value
                    .toLowerCase()
                    .trim();





                let visibleCount = 0;





                positionRows.forEach(row => {




                    const text = row.textContent
                        .toLowerCase();





                    if (text.includes(keyword)) {



                        row.style.display = "";

                        visibleCount++;




                    } else {



                        row.style.display = "none";



                    }




                });





                if (noPositionResult) {



                    noPositionResult.style.display =


                        visibleCount === 0 && keyword !== ""

                            ? ""

                            : "none";



                }





            }
        );



    }
);

/*
==========================================================
                    CONTRACT SEARCH MODULE
==========================================================

USED BY:
- contracts.php


FUNCTION:
- Search employee contract records


REQUIRED:

ID:

#contractSearch

#contractTableBody


CLASS:

.contract-row


EMPTY RESULT:

#noContractResult


==========================================================
*/


document.addEventListener(
    "DOMContentLoaded",
    () => {


        const contractSearch = document.getElementById(
            "contractSearch"
        );



        const contractTableBody = document.getElementById(
            "contractTableBody"
        );





        if (!contractSearch || !contractTableBody) return;





        const contractRows =

            contractTableBody.querySelectorAll(
                ".contract-row"
            );





        const noContractResult = document.getElementById(
            "noContractResult"
        );





        contractSearch.addEventListener(
            "keyup",
            function () {




                const keyword = this.value
                    .toLowerCase()
                    .trim();





                let visibleCount = 0;





                contractRows.forEach(row => {




                    const text = row.textContent
                        .toLowerCase();





                    if (text.includes(keyword)) {



                        row.style.display = "";

                        visibleCount++;




                    } else {



                        row.style.display = "none";



                    }




                });





                if (noContractResult) {



                    noContractResult.style.display =


                        visibleCount === 0

                            ? ""

                            : "none";



                }





            }
        );



    }
);