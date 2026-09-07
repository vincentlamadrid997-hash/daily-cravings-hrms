document.addEventListener("DOMContentLoaded", function () {



    const togglePassword = document.getElementById("togglePassword");
    const password = document.getElementById("password");

    if (togglePassword && password) {

        togglePassword.onclick = function () {

            if (password.type === "password") {

                password.type = "text";
                this.innerHTML = '<i class="fa-solid fa-eye-slash"></i>';

            } else {

                password.type = "password";
                this.innerHTML = '<i class="fa-solid fa-eye"></i>';

            }

        };

    }



    const toggleConfirmPassword = document.getElementById("toggleConfirmPassword");
    const confirmPassword = document.getElementById("confirm_password");

    if (toggleConfirmPassword && confirmPassword) {

        toggleConfirmPassword.onclick = function () {

            if (confirmPassword.type === "password") {

                confirmPassword.type = "text";
                this.innerHTML = '<i class="fa-solid fa-eye-slash"></i>';

            } else {

                confirmPassword.type = "password";
                this.innerHTML = '<i class="fa-solid fa-eye"></i>';

            }

        };

    }



    const email = document.getElementById("email");
    const emailMessage = document.getElementById("emailMessage");
    const emailBox = document.getElementById("emailBox");

    if (email && emailMessage) {

        email.addEventListener("input", function () {

            const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (this.value.trim() === "") {

                emailMessage.style.display = "none";

                if (emailBox) {
                    emailBox.classList.remove("error", "success");
                }

                return;
            }

            if (pattern.test(this.value)) {

                emailMessage.style.display = "block";
                emailMessage.className = "validation-message success";
                emailMessage.innerHTML = "✓ Valid Email Address";

                if (emailBox) {
                    emailBox.classList.remove("error");
                    emailBox.classList.add("success");
                }

            } else {

                emailMessage.style.display = "block";
                emailMessage.className = "validation-message error";
                emailMessage.innerHTML = "✕ Invalid Email Address";

                if (emailBox) {
                    emailBox.classList.remove("success");
                    emailBox.classList.add("error");
                }

            }

        });

    }



    const strength = document.getElementById("strength");

    if (password && strength) {

        password.addEventListener("input", function () {

            let value = this.value;

            if (value.length === 0) {

                strength.innerHTML = "";
                strength.className = "password-strength";

                return;

            }

            if (value.length < 6) {

                strength.innerHTML = "🔴 Weak password";
                strength.className = "password-strength weak";

            } else if (
                value.length >= 8 &&
                value.match(/[A-Z]/) &&
                value.match(/[0-9]/) &&
                value.match(/[!@#$%^&*]/)
            ) {

                strength.innerHTML = "🟢 Strong password";
                strength.className = "password-strength strong";

            } else {

                strength.innerHTML = "🟠 Medium password";
                strength.className = "password-strength medium";

            }

        });

    }



    const confirmMessage = document.getElementById("confirmMessage");

    if (password && confirmPassword && confirmMessage) {

        confirmPassword.addEventListener("input", function () {

            if (this.value === "") {

                confirmMessage.innerHTML = "";
                return;

            }

            if (password.value === this.value) {

                confirmMessage.innerHTML = "🟢 Passwords match";
                confirmMessage.className = "password-match success";

            } else {

                confirmMessage.innerHTML = "🔴 Passwords do not match";
                confirmMessage.className = "password-match error";

            }

        });

    }



    const alerts = document.querySelectorAll(".alert-error, .alert-success");

    alerts.forEach(function (alert) {

        setTimeout(function () {

            alert.style.opacity = "0";
            alert.style.transform = "translateY(-10px)";

            setTimeout(function () {
                alert.remove();
            }, 500);

        }, 3000);

    });

});