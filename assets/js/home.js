document.addEventListener("DOMContentLoaded", function () {



    const navbar = document.querySelector(".navbar");

    window.addEventListener("scroll", function () {

        if (window.scrollY > 50) {
            navbar.classList.add("scrolled");
        } else {
            navbar.classList.remove("scrolled");
        }

    });



    const elements = document.querySelectorAll(
        ".feature-card, .about-card, .hero-left, .hero-right"
    );

    elements.forEach(function (element) {
        element.classList.add("fade-up");
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

    elements.forEach(function (element) {
        observer.observe(element);
    });



    const buttons = document.querySelectorAll(
        ".btn-primary, .btn-secondary, .login-btn-nav"
    );

    buttons.forEach(function (button) {

        button.addEventListener("click", function (e) {

            const ripple = document.createElement("span");

            const rect = button.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);

            ripple.style.width = size + "px";
            ripple.style.height = size + "px";

            ripple.style.left =
                e.clientX - rect.left - size / 2 + "px";

            ripple.style.top =
                e.clientY - rect.top - size / 2 + "px";

            ripple.classList.add("ripple");

            button.appendChild(ripple);

            setTimeout(function () {
                ripple.remove();
            }, 600);

        });

    });



    const counters = document.querySelectorAll(".hero-info h2");

    counters.forEach(function (counter) {

        const target = parseInt(counter.innerText);
        const suffix = counter.innerText.replace(/[0-9]/g, "");

        let current = 0;
        const speed = target / 40;

        function updateCounter() {

            current += speed;

            if (current < target) {

                counter.innerText =
                    Math.floor(current) + suffix;

                requestAnimationFrame(updateCounter);

            } else {

                counter.innerText =
                    target + suffix;

            }

        }

        updateCounter();

    });



    const cards = document.querySelectorAll(".feature-card");

    cards.forEach(function (card) {

        card.addEventListener("mousemove", function (e) {

            const rect = card.getBoundingClientRect();

            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            card.style.setProperty("--x", x + "px");
            card.style.setProperty("--y", y + "px");

        });

    });



    document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {

        anchor.addEventListener("click", function (e) {

            const target = document.querySelector(
                this.getAttribute("href")
            );

            if (target) {

                e.preventDefault();

                target.scrollIntoView({
                    behavior: "smooth"
                });

            }

        });

    });

});