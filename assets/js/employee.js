/*
==========================================================
        EMPLOYEE ATTENDANCE HISTORY JS
==========================================================
*/

document.addEventListener("DOMContentLoaded", function () {

    const today = new Date()
        .toISOString()
        .split("T")[0];

    const fromInput = document.getElementById("date_from");
    const toInput = document.getElementById("date_to");
    const filterForm = document.getElementById("attendanceFilterForm");
    const exportBtn = document.getElementById("exportPdfBtn");
    const pdfForm = document.getElementById("pdfExportForm");


    if (fromInput) {
        fromInput.max = today;
    }

    if (toInput) {
        toInput.max = today;
    }


    if (filterForm) {

        filterForm.addEventListener("submit", function (e) {

            const from = fromInput.value;
            const to = toInput.value;

            if (from && from > today) {

                e.preventDefault();

                Swal.fire({
                    icon: "warning",
                    title: "Invalid Date",
                    text: "Future dates are not allowed."
                });

                return;
            }

            if (to && to > today) {

                e.preventDefault();

                Swal.fire({
                    icon: "warning",
                    title: "Invalid Date",
                    text: "Future dates are not allowed."
                });

                return;
            }

            if (from && to && from > to) {

                e.preventDefault();

                Swal.fire({
                    icon: "error",
                    title: "Invalid Date Range",
                    text: "From Date cannot be later than To Date."
                });

                return;
            }

        });

    }


    /*
    ==========================================================
                EXPORT PDF VALIDATION
    ==========================================================
    */

    if (exportBtn) {

        exportBtn.addEventListener("click", function () {

            const from = fromInput.value;
            const to = toInput.value;


            if (from === "" || to === "") {

                Swal.fire({
                    icon: "warning",
                    title: "Date Required",
                    text: "Please select From Date and To Date before exporting PDF.",
                    confirmButtonColor: "#003DA5"
                });

                return;
            }


            if (from > to) {

                Swal.fire({
                    icon: "error",
                    title: "Invalid Date Range",
                    text: "From Date cannot be later than To Date.",
                    confirmButtonColor: "#003DA5"
                });

                return;
            }


            document.getElementById("pdf_month").value =
                document.querySelector('input[name="month"]').value;


            document.getElementById("pdf_date_from").value = from;


            document.getElementById("pdf_date_to").value = to;


            document.getElementById("pdf_status").value =
                document.querySelector('select[name="status"]').value;


            pdfForm.submit();

        });

    }

});










/*
==========================================================
            EMPLOYEE PAYROLL JS
==========================================================
*/

document.addEventListener(
    "DOMContentLoaded",
    function(){

        const pdfButtons = document.querySelectorAll(
            ".employee-payroll-pdf-btn"
        );

        pdfButtons.forEach(function(button){

            button.addEventListener(
                "click",
                function(e){

                    const form = this.closest("form");

                    if(!form){

                        e.preventDefault();

                        Swal.fire({

                            icon: "warning",

                            title: "Payslip Not Available",

                            text: "Your payslip is not yet generated."

                        });

                        return;

                    }

                }

            );

        });

        const emptyPayroll = document.querySelector(
            ".employee-payroll-empty"
        );

        if(emptyPayroll){

            console.log(
                "No payroll records available."
            );

        }

    }

);









/*
==========================================================
        EMPLOYEE SUBMIT LEAVE REQUEST 
==========================================================
*/

document.addEventListener("DOMContentLoaded", function(){

    const form = document.getElementById(
        "employeeLeaveRequestForm"
    );

    const leaveType = document.getElementById(
        "leave_type"
    );

    const otherLeaveWrapper = document.getElementById(
        "otherLeaveWrapper"
    );

    const otherLeaveInput = document.getElementById(
        "other_leave_type"
    );

    const startDate = document.getElementById(
        "start_date"
    );

    const endDate = document.getElementById(
        "end_date"
    );

    const leaveDays = document.getElementById(
        "leave_days"
    );

    const reason = document.getElementById(
        "leave_reason"
    );

    const reasonCounter = document.getElementById(
        "reasonCounter"
    );

    const attachment = document.getElementById(
        "attachment"
    );

    const resetButton = document.querySelector(
        ".employee-leave-reset-btn"
    );


    const holidayList = typeof holidays !== "undefined"
        ? holidays
        : [];


    const today = new Date()
        .toISOString()
        .split("T")[0];


    if(startDate){

        startDate.min = today;

    }


    if(endDate){

        endDate.min = today;

    }


    function showError(
        input,
        message,
        errorID
    ){

        if(input){

            input.classList.add(
                "input-error"
            );

        }


        const error =
            document.getElementById(errorID);


        if(error){

            error.innerHTML = message;

        }

    }


    function removeError(
        input,
        errorID
    ){

        if(input){

            input.classList.remove(
                "input-error"
            );

        }


        const error =
            document.getElementById(errorID);


        if(error){

            error.innerHTML = "";

        }

    }

        if(leaveType){

        leaveType.addEventListener(
            "change",
            function(){

                removeError(
                    leaveType,
                    "leaveTypeError"
                );


                if(this.value === "others"){

                    otherLeaveWrapper.style.display =
                        "block";

                    otherLeaveInput.required =
                        true;

                }

                else{

                    otherLeaveWrapper.style.display =
                        "none";

                    otherLeaveInput.required =
                        false;

                    otherLeaveInput.value = "";

                    removeError(
                        otherLeaveInput,
                        "otherLeaveError"
                    );

                }

            }

        );

    }


    if(otherLeaveInput){

        otherLeaveInput.addEventListener(
            "input",
            function(){

                removeError(
                    otherLeaveInput,
                    "otherLeaveError"
                );

            }

        );

    }


    function validateDateRange(){

        if(
            startDate.value &&
            endDate.value
        ){

            const start =
                new Date(startDate.value);

            const end =
                new Date(endDate.value);

            if(end < start){

                showError(
                    endDate,
                    "End date cannot be earlier than start date.",
                    "endDateError"
                );

                leaveDays.value = "";
                return false;

            }

        }


        removeError(
            endDate,
            "endDateError"
        );

        return true;

    }


    function calculateDays(){

        if(
            !startDate.value ||
            !endDate.value
        ){

            leaveDays.value = "";
            return;

        }

        const start =
            new Date(startDate.value);

        const end =
            new Date(endDate.value);

        if(end < start){

            leaveDays.value = "";
            return;

        }


        let count = 0;

        let current =
            new Date(start);

        while(current <= end){

            const day =
                current.getDay();

            const currentDate =
                current.toISOString()
                    .split("T")[0];

            const isWeekend =
                day === 0 ||
                day === 6;

            const isHoliday =
                holidayList.includes(
                    currentDate
                );

            if(
                !isWeekend &&
                !isHoliday
            ){

                count++;

            }

            current.setDate(
                current.getDate()+1
            );

        }

        leaveDays.value = count;

    }

        if(startDate){

        startDate.addEventListener(
            "change",
            function(){

                endDate.min =
                    startDate.value;

                removeError(
                    startDate,
                    "startDateError"
                );

                validateDateRange();

                calculateDays();

            }

        );

    }


    if(endDate){

        endDate.addEventListener(
            "change",
            function(){

                removeError(
                    endDate,
                    "endDateError"
                );

                validateDateRange();

                calculateDays();

            }

        );

    }


    function updateCounter(){

        if(
            reasonCounter &&
            reason
        ){

            reasonCounter.innerHTML =
                reason.value.length;

        }

    }


    if(reason){

        reason.addEventListener(
            "input",
            function(){

                updateCounter();

                removeError(
                    reason,
                    "reasonError"
                );

            }

        );

    }


    if(attachment){

        attachment.addEventListener(
            "change",
            function(){

                if(!this.files.length){
                    return;

                }

                const file =
                    this.files[0];

                const allowed = [

                    "application/pdf",
                    "image/jpeg",
                    "image/png",
                    "application/msword",
                    "application/vnd.openxmlformats-officedocument.wordprocessingml.document"

                ];


                if(!allowed.includes(file.type)){

                    showError(
                        attachment,
                        "Invalid file type.",
                        "attachmentError"
                    );

                    this.value = "";
                    return;

                }


                if(
                    file.size >
                    5 * 1024 * 1024
                ){


                    showError(
                        attachment,
                        "File size must not exceed 5MB.",
                        "attachmentError"
                    );

                    this.value = "";
                    return;

                }

                removeError(
                    attachment,
                    "attachmentError"
                );

            }

        );

    }


    if(form){

        form.addEventListener(
            "submit",
            function(e){

                e.preventDefault();

                let valid = true;

                if(!leaveType.value){

                    showError(
                        leaveType,
                        "Please select leave type.",
                        "leaveTypeError"
                    );

                    valid = false;

                }

                if(
                    leaveType.value === "others"
                    &&
                    otherLeaveInput.value.trim() === ""
                ){

                    showError(
                        otherLeaveInput,
                        "Please specify leave type.",
                        "otherLeaveError"
                    );

                    valid = false;

                }

                if(!startDate.value){

                    showError(
                        startDate,
                        "Start date is required.",
                        "startDateError"
                    );

                    valid = false;

                }


                if(!endDate.value){

                    showError(
                        endDate,
                        "End date is required.",
                        "endDateError"
                    );

                    valid = false;

                }

                if(
                    !validateDateRange()
                ){

                    valid = false;

                }

                if(
                    !reason.value.trim()
                ){

                    showError(
                        reason,
                        "Reason is required.",
                        "reasonError"
                    );

                    valid = false;

                }

                if(
                    reason.value.trim().length < 10
                ){

                    showError(
                        reason,
                        "Reason must contain at least 10 characters.",
                        "reasonError"
                    );

                    valid = false;

                }

                if(!valid){

                    return;

                }

                Swal.fire({

                    title:
                        "Submit Leave Request?",
                    text:
                        "Your request will be sent for approval.",
                    icon:
                        "question",

                    showCancelButton:true,

                    confirmButtonColor:
                        "#0D47A1",

                    cancelButtonColor:
                        "#9E9E9E",

                    confirmButtonText:
                        "Submit"

                })
                .then(
                    (result)=>{

                        if(result.isConfirmed){

                            form.submit();

                        }

                    }

                );

            }

        );

    }


    if(resetButton){
    

        resetButton.addEventListener(
            "click",
            function(){

                setTimeout(
                    function(){

                        document
                            .querySelectorAll(
                                ".input-error"
                            )
                            .forEach(
                                function(element){

                                    element.classList.remove(
                                        "input-error"
                                    );

                                }

                            );

                        document
                            .querySelectorAll(
                                ".employee-input-error"
                            )
                            .forEach(
                                function(error){

                                    error.innerHTML = "";

                                }

                            );

                        if(reasonCounter){

                            reasonCounter.innerHTML = "0";

                        }

                        if(otherLeaveWrapper){

                            otherLeaveWrapper.style.display =
                                "none";

                        }

                        if(otherLeaveInput){

                            otherLeaveInput.value = "";

                        }

                        if(leaveDays){

                            leaveDays.value = "";

                        }

                    },
                    50
                );

            }

        );

    }

});










/*
==========================================================
            EMPLOYEE LEAVE HISTORY
==========================================================
*/

document.addEventListener(
    "DOMContentLoaded",
    function(){

        const searchInput = document.getElementById(
            "leaveSearch"
        );

        const statusFilter = document.getElementById(
            "statusFilter"
        );

        const table = document.getElementById(
            "leaveHistoryTable"
        );

        const searchEmptyRow = document.getElementById(
            "employeeSearchEmpty"
        );

        if(!table){

            return;

        }


        const tableBody = table.querySelector(
            "tbody"
        );

        const rows = tableBody.querySelectorAll(
            "tr"
        );


        function filterLeaveHistory(){

            let searchValue = "";
            let selectedStatus = "";

            if(searchInput){

                searchValue = searchInput.value
                    .toLowerCase()
                    .trim();

            }

            if(statusFilter){

                selectedStatus = statusFilter.value
                    .toLowerCase()
                    .trim();

            }

            let visibleRows = 0;

            rows.forEach(
                function(row){

                    if(
                        row.id === "employeeSearchEmpty"
                    ){

                        return;

                    }

                    if(
                        row.querySelector(
                            ".employee-leave-empty"
                        )
                    ){

                        return;

                    }

                    const rowText = row.textContent
                        .toLowerCase();

                    const statusElement =
                    row.querySelector(
                        ".employee-status-badge"
                    );

                    let rowStatus = "";

                    if(statusElement){

                        rowStatus =
                        statusElement.textContent
                            .toLowerCase()
                            .trim();

                    }

                    const searchMatch =
                    rowText.includes(
                        searchValue
                    );

                    const statusMatch =

                    selectedStatus === ""
                    ||

                    rowStatus.includes(
                        selectedStatus
                    );

                    if(
                        searchMatch
                        &&
                        statusMatch
                    ){

                        row.style.display = "";
                        visibleRows++;

                    }

                    else{

                        row.style.display = "none";

                    }

                }
            );


            if(searchEmptyRow){

                if(

                    visibleRows === 0
                    &&

                    (

                        searchValue !== ""
                        ||
                        selectedStatus !== ""

                    )

                ){

                    searchEmptyRow.style.display =
                    "table-row";

                }

                else{

                    searchEmptyRow.style.display =
                    "none";

                }

            }

        }


        if(searchInput){

            searchInput.addEventListener(

                "keyup",
                filterLeaveHistory

            );

        }

        if(statusFilter){

            statusFilter.addEventListener(

                "change",
                filterLeaveHistory

            );

        }

    }
);










/*
==========================================================
        EMPLOYEE RESIGNATION REQUEST
==========================================================
*/

document.addEventListener(
    "DOMContentLoaded",
    function(){

        const form = document.getElementById(
            "employeeResignationForm"
        );

        const resignationDate = document.getElementById(
            "resignation_date"
        );

        const lastWorkingDay = document.getElementById(
            "last_working_day"
        );

        const reason = document.getElementById(
            "resignation_reason"
        );

        const reasonCounter = document.getElementById(
            "reasonCounter"
        );

        const resetButton = document.querySelector(
            ".employee-resignation-reset-btn"
        );

        const submitButton = document.querySelector(
            ".employee-resignation-submit-btn"
        );


        const today = new Date()
            .toISOString()
            .split("T")[0];

        let isSubmitting = false;


        if(resignationDate){

            resignationDate.min = today;

        }

        if(lastWorkingDay){

            lastWorkingDay.min = today;

        }


        function showError(
            input,
            message,
            errorID
        ){

            if(input){

                input.classList.add(
                    "input-error"
                );

            }

            const error =
                document.getElementById(errorID);

            if(error){

                error.innerHTML =
                    message;

            }

        }


        function removeError(
            input,
            errorID
        ){

            if(input){

                input.classList.remove(
                    "input-error"
                );

            }

            const error =
                document.getElementById(errorID);

            if(error){

                error.innerHTML = "";

            }

        }


        function clearAllErrors(){

            document
            .querySelectorAll(
                ".input-error"
            )
            .forEach(
                function(element){

                    element.classList.remove(
                        "input-error"
                    );

                }
            );


            document
            .querySelectorAll(
                ".employee-input-error, .employee-resignation-error"
            )
            .forEach(
                function(error){

                    error.innerHTML = "";

                }
            );


        }


        function validateWorkingDay(){

            if(
                resignationDate.value &&
                lastWorkingDay.value
            ){

                const resignation =
                    new Date(
                        resignationDate.value
                    );

                const lastDay =
                    new Date(
                        lastWorkingDay.value
                    );

                if(lastDay < resignation){

                    showError(

                        lastWorkingDay,
                        "Last working day cannot be earlier than resignation date.",
                        "lastWorkingDayError"

                    );

                    return false;

                }

            }

            removeError(

                lastWorkingDay,
                "lastWorkingDayError"

            );

            return true;

        }


        if(resignationDate){

            resignationDate.addEventListener(

                "change",

                function(){

                    removeError(

                        resignationDate,
                        "resignationDateError"

                    );

                    validateWorkingDay();

                }

            );

        }


        if(lastWorkingDay){

            lastWorkingDay.addEventListener(

                "change",

                function(){

                    removeError(

                        lastWorkingDay,
                        "lastWorkingDayError"

                    );

                    validateWorkingDay();

                }

            );

        }


        if(resignationDate){

            resignationDate.addEventListener(

                "input",

                function(){

                    if(
                        this.value < today
                    ){

                        showError(

                            resignationDate,
                            "Resignation date cannot be before today.",
                            "resignationDateError"

                        );

                        this.value = "";

                    }

                }

            );

        }

        if(lastWorkingDay){

            lastWorkingDay.addEventListener(

                "input",

                function(){

                    if(
                        this.value < today
                    ){


                        showError(

                            lastWorkingDay,
                            "Last working day cannot be before today.",
                            "lastWorkingDayError"

                        );

                        this.value = "";

                    }

                }

            );

        }


        function updateCounter(){

            if(
                reason &&
                reasonCounter
            ){

                reasonCounter.innerHTML =
                    reason.value.length;

            }

        }


        if(reason){

            reason.addEventListener(

                "input",

                function(){

                    updateCounter();

                    removeError(

                        reason,
                        "reasonError"

                    );

                }

            );

        }


        document
        .querySelectorAll(
            ".employee-resignation-form-group input, .employee-resignation-form-group textarea"
        )
        .forEach(

            function(input){

                input.addEventListener(

                    "input",

                    function(){

                        this.classList.remove(
                            "input-error"
                        );

                    }

                );

            }

        );


        if(form){

            form.addEventListener(

                "submit",

                function(e){

                    e.preventDefault();

                    if(isSubmitting){

                        return;

                    }

                    let valid = true;

                    if(
                        !resignationDate.value
                    ){

                        showError(

                            resignationDate,
                            "Resignation date is required.",
                            "resignationDateError"

                        );

                        valid = false;

                    }


                    if(
                        !lastWorkingDay.value
                    ){

                        showError(

                            lastWorkingDay,
                            "Last working day is required.",
                            "lastWorkingDayError"

                        );

                        valid = false;

                    }

                    if(
                        !validateWorkingDay()
                    ){

                        valid = false;

                    }

                    if(
                        !reason.value.trim()
                    ){

                        showError(

                            reason,
                            "Reason is required.",
                            "reasonError"

                        );

                        valid = false;

                    }

                    if(
                        reason.value.trim().length < 10
                    ){

                        showError(

                            reason,
                            "Reason must contain at least 10 characters.",
                            "reasonError"
                        );

                        valid = false;

                    }

                    if(!valid){

                        return;

                    }

                    Swal.fire({

                        title:
                        "Submit Resignation Request?",

                        text:
                        "Your resignation request will be sent to HR for approval.",

                        icon:
                        "question",

                        showCancelButton:true,

                        confirmButtonColor:
                        "#0D47A1",

                        cancelButtonColor:
                        "#9E9E9E",

                        confirmButtonText:
                        "Submit Request"

                    })

                    .then(

                        function(result){

                            if(result.isConfirmed){

                                isSubmitting = true;

                                if(submitButton){

                                    submitButton.disabled = true;

                                    submitButton.innerHTML = `

                                        <i class="fa-solid fa-spinner fa-spin"></i>
                                        Submitting...

                                    `;

                                }


                                form.submit();

                            }

                        }

                    );

                }

            );

        }


        if(resetButton){

            resetButton.addEventListener(

                "click",

                function(){

                    setTimeout(

                        function(){

                            clearAllErrors();

                            updateCounter();

                            if(reasonCounter){

                                reasonCounter.innerHTML =
                                    "0";

                            }

                            if(resignationDate){

                                resignationDate.min =
                                    today;

                            }

                            if(lastWorkingDay){

                                lastWorkingDay.min =
                                    today;

                            }

                        },

                        50

                    );

                }

            );

        }

    }

);









/*
==========================================================
        EMPLOYEE RESIGNATION HISTORY JS

        Daily Cravings Foods Inc.

==========================================================
*/


document.addEventListener(

    "DOMContentLoaded",

    function(){





        /*
        ==================================================
                ELEMENTS
        ==================================================
        */


        const searchInput = document.getElementById(

            "resignationSearch"

        );



        const statusFilter = document.getElementById(

            "resignationStatusFilter"

        );



        const table = document.getElementById(

            "resignationHistoryTable"

        );



        const emptyRow = document.getElementById(

            "employeeResignationSearchEmpty"

        );





        if(!table){

            return;

        }






        const rows = table.querySelectorAll(

            "tbody tr:not(#employeeResignationSearchEmpty)"

        );







        /*
        ==================================================
                FILTER FUNCTION
        ==================================================
        */


        function filterTable(){



            let searchValue = "";



            if(searchInput){


                searchValue = searchInput.value

                    .toLowerCase()

                    .trim();


            }






            let selectedStatus = "";



            if(statusFilter){


                selectedStatus = statusFilter.value

                    .toLowerCase();


            }







            let visibleCount = 0;







            rows.forEach(

                function(row){





                    const rowText = row.innerText

                        .toLowerCase();





                    const statusElement = row.querySelector(

                        ".employee-resignation-status"

                    );





                    let rowStatus = "";





                    if(statusElement){



                        rowStatus = statusElement.innerText

                            .toLowerCase()

                            .trim();



                    }







                    const matchSearch =

                        rowText.includes(

                            searchValue

                        );








                    const matchStatus =

                        selectedStatus === ""

                        ||

                        rowStatus === selectedStatus;









                    if(

                        matchSearch

                        &&

                        matchStatus

                    ){



                        row.style.display = "";



                        visibleCount++;



                    }

                    else{



                        row.style.display = "none";



                    }






                }

            );







            if(emptyRow){



                if(visibleCount === 0){



                    emptyRow.style.display = "";



                }

                else{



                    emptyRow.style.display = "none";



                }



            }






        }









        /*
        ==================================================
                SEARCH EVENT
        ==================================================
        */


        if(searchInput){



            searchInput.addEventListener(

                "input",

                function(){



                    filterTable();



                }

            );



        }









        /*
        ==================================================
                STATUS FILTER EVENT
        ==================================================
        */


        if(statusFilter){



            statusFilter.addEventListener(

                "change",

                function(){



                    filterTable();



                }

            );



        }






    }


);