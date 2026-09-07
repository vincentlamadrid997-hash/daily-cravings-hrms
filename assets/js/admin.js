/*
==========================================================
                    USER SEARCH
==========================================================
*/
const userSearch = document.getElementById("userSearch");

if (userSearch) {

    userSearch.addEventListener("keyup", function () {

        const keyword = this.value.toLowerCase();

        const rows = document.querySelectorAll(".user-row");

        let visible = 0;

        rows.forEach(function (row) {

            if (row.innerText.toLowerCase().indexOf(keyword) > -1) {

                row.style.display = "";

                visible++;

            } else {

                row.style.display = "none";

            }

        });

        const empty = document.getElementById("noUserResult");

        if (empty) {

            empty.style.display = visible === 0 ? "" : "none";

        }

    });

}










/*
==========================================================
                SWEETALERT SUCCESS / ERROR
==========================================================
*/
const userAlert = document.getElementById("userAlertData");

if (userAlert) {

    const success = userAlert.dataset.success;

    const error = userAlert.dataset.error;

    if (success !== "") {

        Swal.fire({

            icon: "success",
            title: "Success",
            text: success,
            confirmButtonColor: "#003DA5"

        });

    }

    if (error !== "") {

        Swal.fire({

            icon: "error",
            title: "Error",
            text: error,
            confirmButtonColor: "#C62828"

        });

    }

}










/*
==========================================================
            ACTIVATE / DEACTIVATE CONFIRMATION
==========================================================
*/
document.querySelectorAll('form[action="user_status.php"]').forEach(function(form){

    form.addEventListener("submit", function(e){

        e.preventDefault();

        const button = form.querySelector("button");

        const deactivate = button.classList.contains("active");

        Swal.fire({

            title: deactivate
                ? "Deactivate User?"
                : "Activate User?",

            text: deactivate
                ? "This user will no longer be able to log in."
                : "This user will be allowed to log in again.",

            icon: "question",

            showCancelButton: true,

            confirmButtonColor: deactivate
                ? "#C62828"
                : "#2E7D32",

            cancelButtonColor: "#90A4AE",

            confirmButtonText: deactivate
                ? "Yes, Deactivate"
                : "Yes, Activate"

        }).then((result)=>{

            if(result.isConfirmed){

                form.submit();

            }

        });

    });

});

/*
==========================================================
    ACTIVATE / DEACTIVATE CONFIRMATION
    (EMPLOYEES, DEPARTMENTS, POSITIONS)
==========================================================
*/
document.querySelectorAll(
    'form[action="employee_status.php"], form[action="department_status.php"], form[action="position_status.php"]'
).forEach(function(form){

    form.addEventListener("submit", function(e){

        e.preventDefault();

        const button = form.querySelector("button");

        const deactivate = button.classList.contains("active");

        const label = button.getAttribute("title");

        Swal.fire({

            title: label + "?",

            text: deactivate
                ? "This record will be marked Inactive."
                : "This record will be marked Active.",

            icon: "question",

            showCancelButton: true,

            confirmButtonColor: deactivate
                ? "#C62828"
                : "#2E7D32",

            cancelButtonColor: "#90A4AE",

            confirmButtonText: deactivate
                ? "Yes, Deactivate"
                : "Yes, Activate"

        }).then((result)=>{

            if(result.isConfirmed){

                form.submit();

            }

        });

    });

});










/*
==========================================================
            EMPLOYEE AUTO FILL
==========================================================
*/
const employeeSelect = document.getElementById("employee_id");
const fullNameInput = document.getElementById("full_name");
const emailInput = document.getElementById("email");
const passwordInput = document.getElementById("password");
const confirmPasswordInput = document.getElementById("confirm_password");


if(employeeSelect){

    employeeSelect.addEventListener(
        "change",
        function(){

            const option =
            this.options[this.selectedIndex];

            const employeeName =
            option.getAttribute("data-name");

            const employeeEmail =
            option.getAttribute("data-email");

            if(employeeName && employeeEmail){

                fullNameInput.value =
                employeeName;

                emailInput.value =
                employeeEmail;





                /*
                ==========================================
                    TEMPORARY PASSWORD
                ==========================================
                */
                const firstName =
                employeeName
                .split(" ")[0]
                .toLowerCase();

                const temporaryPassword =
                firstName + "@2026";

                passwordInput.value =
                temporaryPassword;

                confirmPasswordInput.value =
                temporaryPassword;

            }
            else{

                fullNameInput.value = "";
                emailInput.value = "";
                passwordInput.value = "";
                confirmPasswordInput.value = "";

            }

        }

    );

}










/*
==========================================================
                PASSWORD SHOW / HIDE
==========================================================
*/
const togglePassword =
document.getElementById("togglePassword");

const toggleConfirmPassword =
document.getElementById("toggleConfirmPassword");

const passwordIcon =
document.querySelector("#togglePassword i");

const confirmPasswordIcon =
document.querySelector("#toggleConfirmPassword i");

if(togglePassword){

    togglePassword.addEventListener(
        "click",
        function(){

            if(passwordInput.type === "password"){

                passwordInput.type =
                "text";

                passwordIcon.classList.remove(
                    "fa-eye"
                );

                passwordIcon.classList.add(
                    "fa-eye-slash"
                );

            }
            else{

                passwordInput.type =
                "password";

                passwordIcon.classList.remove(
                    "fa-eye-slash"
                );

                passwordIcon.classList.add(
                    "fa-eye"
                );

            }

        }

    );

}

if(toggleConfirmPassword){

    toggleConfirmPassword.addEventListener(
        "click",
        function(){

            if(confirmPasswordInput.type === "password"){

                confirmPasswordInput.type =
                "text";

                confirmPasswordIcon.classList.remove(
                    "fa-eye"
                );

                confirmPasswordIcon.classList.add(
                    "fa-eye-slash"
                );

            }
            else{

                confirmPasswordInput.type =
                "password";

                confirmPasswordIcon.classList.remove(
                    "fa-eye-slash"
                );

                confirmPasswordIcon.classList.add(
                    "fa-eye"
                );

            }

        }

    );

}










 /*
==========================================================
                REQUIRED VALIDATION
==========================================================
*/
const userForm =
document.getElementById("userForm");

if(userForm){

    userForm.addEventListener(
        "submit",
        function(e){

            let isValid = true;

            const requiredFields =
            userForm.querySelectorAll("[required]");

            requiredFields.forEach(
                function(field){

                    const error =
                    field.parentElement.querySelector(
                        ".admin-error-message"
                    );

                    if(field.value.trim() === ""){

                        isValid = false;

                        field.classList.add(
                            "input-error"
                        );

                        if(error){

                            error.innerHTML =
                            "This field is required.";

                            error.style.display =
                            "block";

                        }

                    }
                    else{

                        field.classList.remove(
                            "input-error"
                        );

                        if(error){

                            error.style.display =
                            "none";

                        }

                    }

                }

            );










                        /*
            ==========================================
                PASSWORD MATCH CHECK
                (only runs if confirm_password exists
                and is actually enabled — user_edit.php
                has no such field, and Add User's copy
                is intentionally disabled/auto-filled)
            ==========================================
            */
            if(
                confirmPasswordInput &&
                !confirmPasswordInput.disabled &&
                passwordInput.value !==
                confirmPasswordInput.value
            ){

                isValid = false;

                confirmPasswordInput.classList.add(
                    "input-error"
                );

                const error =
                confirmPasswordInput.parentElement.querySelector(
                    ".admin-error-message"
                );

                if(error){

                    error.innerHTML =
                    "Password does not match."; 

                    error.style.display =
                    "block";

                }

            }

                        if(!isValid){

                e.preventDefault();
                return;

            }

            e.preventDefault();

            Swal.fire({
                icon: "question",
                title: "Add this user?",
                text: "This will create a new system account.",
                showCancelButton: true,
                confirmButtonText: "Yes, Add User",
                cancelButtonText: "Cancel",
                confirmButtonColor: "#4a6cf7",
                reverseButtons: true
            }).then(function (result) {

                if (result.isConfirmed) {
                    userForm.submit();
                }

            });

        }

    );

}










 /*
==========================================================
            REMOVE ERROR WHILE TYPING
==========================================================
*/
document.querySelectorAll(
    ".admin-user-group input, .admin-user-group select"
)

.forEach(
    function(field){

        field.addEventListener(
            "input",
            function(){

                if(this.value.trim() !== ""){

                    this.classList.remove(
                        "input-error"
                    );

                    const error =
                    this.parentElement.querySelector(
                        ".admin-error-message"
                    );

                    if(error){

                        error.style.display =
                        "none";

                    }

                }

            }

        );

        field.addEventListener(
            "change",
            function(){

                if(this.value.trim() !== ""){

                    this.classList.remove(
                        "input-error"
                    );

                    const error =
                    this.parentElement.querySelector(
                        ".admin-error-message"
                    );

                    if(error){

                        error.style.display =
                        "none";

                    }

                }

            }

        );

    }

);











/*
==========================================================
                IMAGE PREVIEW
==========================================================
*/
const profileInput =
document.getElementById("profile_picture");

const previewImage =
document.getElementById("previewImage");

if(profileInput){

    profileInput.addEventListener(
        "change",
        function(){

            const file =
            this.files[0];

            if(file){

                const reader =
                new FileReader();

                reader.onload =
                function(e){

                    previewImage.src =
                    e.target.result;

                }

                reader.readAsDataURL(file);

            }

        }

    );

}






















/*
==========================================================
                PROFILE IMAGE FALLBACK
==========================================================
*/


const profileImage = document.querySelector(
    ".admin-user-profile-image img"
);




if(profileImage){


    profileImage.onerror = function(){


        this.src =
        "../assets/images/default-profile.png";


    };


}











