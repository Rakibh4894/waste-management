document.querySelector("#profile-img-file-input") && document.querySelector("#profile-img-file-input").addEventListener("change", function () {
    var e = document.querySelector(".user-profile-image"),
        t = document.querySelector(".profile-img-file-input").files[0], r = new FileReader;
    r.addEventListener("load", function () {
        e.src = r.result;
    }, !1), t && r.readAsDataURL(t);
}),

document.querySelectorAll(".form-steps") && Array.from(document.querySelectorAll(".form-steps")).forEach(function (l) {
    l.querySelectorAll(".nexttab") && Array.from(l.querySelectorAll(".nexttab")).forEach(function (t) {
        var e = l.querySelectorAll('button[data-bs-toggle="pill"]');
        Array.from(e).forEach(function (e) {
            e.addEventListener("show.bs.tab", function (e) {
                e.target.classList.add("done");
            });
        }), t.addEventListener("click", function () {
            l.classList.add("was-validated");

            // Get all form controls in the current tab
            var allValid = true;
            l.querySelectorAll(".tab-pane.show .form-control").forEach(function (e) {

                // Check if the field is required and whether it has a value
                if (e.hasAttribute("required") && e.value.length === 0) {
                    allValid = false; // If a required field is empty, form is not valid
                    e.classList.add("is-invalid"); // Add Bootstrap validation style
                } else {
                    e.classList.remove("is-invalid"); // Remove validation error if it's valid
                }
            });

            // Only proceed to the next tab if all required fields are valid
            if (allValid) {
                e = t.getAttribute("data-nexttab");
                document.getElementById(e).click();
                l.classList.remove("was-validated");
            }
        });
    });

    // Previous tab functionality
    l.querySelectorAll(".previestab") && Array.from(l.querySelectorAll(".previestab")).forEach(function (o) {
        o.addEventListener("click", function () {
            var e = o.getAttribute("data-previous"),
                t = o.closest("form").querySelectorAll(".custom-nav .done").length,
                r = t - 1;
            for (r = t - 1; r < t; r++) {
                o.closest("form").querySelectorAll(".custom-nav .done")[r] && o.closest("form").querySelectorAll(".custom-nav .done")[r].classList.remove("done");
            }
            document.getElementById(e).click();
        });
    });

    // Progress bar and tab navigation updates
    var a = l.querySelectorAll('button[data-bs-toggle="pill"]');
    a && Array.from(a).forEach(function (r, o) {
        r.setAttribute("data-position", o), r.addEventListener("click", function () {
            var e;
            l.classList.remove("was-validated");
            r.getAttribute("data-progressbar") && (e = document.getElementById("custom-progress-bar").querySelectorAll("li").length - 1, e = o / e * 100, document.getElementById("custom-progress-bar").querySelector(".progress-bar").style.width = e + "%");

            0 < l.querySelectorAll(".custom-nav .done").length && Array.from(l.querySelectorAll(".custom-nav .done")).forEach(function (e) {
                e.classList.remove("done");
            });
            for (var t = 0; t <= o; t++) a[t].classList.contains("active") ? a[t].classList.remove("done") : a[t].classList.add("done");
        });
    });


    // Submit button validation for all steps
    if (document.getElementById("updateBtn") != null){
        document.getElementById("updateBtn").addEventListener("click", function (e) {
            var allValid = true;
            var firstInvalidElement = null; // Declare firstInvalidElement here

            // Validate all required fields across all steps
            l.querySelectorAll(".form-control[required]").forEach(function (input) {
                if (input.value.trim() === "") {
                    input.classList.add("is-invalid");
                    allValid = false;

                    // Store the first invalid element if none has been found yet
                    if (!firstInvalidElement) {
                        firstInvalidElement = input;
                    }
                } else {
                    input.classList.remove("is-invalid");
                }
            });

            if (!allValid) {
                // Prevent form submission if there are validation errors
                e.preventDefault();
                toastr.error("Please fill in all required fields.");

                // Jump to the tab containing the first invalid field
                if (firstInvalidElement) {
                    console.log(
                        'hello'
                    )
                    // Find the tab related to the first invalid element
                    var invalidTabPane = firstInvalidElement.closest(".tab-pane");
                    var invalidTabId = invalidTabPane.getAttribute("id");

                    // Find the tab button that corresponds to this tab pane
                    var tabButton = document.querySelector('button[data-bs-target="#' + invalidTabId + '"]');

                    // Trigger a click on the tab button to switch to the first invalid tab
                    if (tabButton) {
                        tabButton.click();
                    }
                }
            }
        });
    }

    // Submit button validation for all steps
    if (document.getElementById("saveBtn") != null) {
        document.getElementById("saveBtn").addEventListener("click", function (e) {
            var allValid = true;
            var firstInvalidElement = null; // Declare firstInvalidElement here

            // Validate all required fields across all steps
            l.querySelectorAll(".form-control[required]").forEach(function (input) {
                if (input.value.trim() === "") {
                    input.classList.add("is-invalid");
                    allValid = false;

                    // Store the first invalid element if none has been found yet
                    if (!firstInvalidElement) {
                        firstInvalidElement = input;
                    }
                } else {
                    input.classList.remove("is-invalid");
                }
            });

            if (!allValid) {
                // Prevent form submission if there are validation errors
                e.preventDefault();
                toastr.error("Please fill in all required fields.");

                // Jump to the tab containing the first invalid field
                if (firstInvalidElement) {
                    console.log(
                        'hello'
                    )
                    // Find the tab related to the first invalid element
                    var invalidTabPane = firstInvalidElement.closest(".tab-pane");
                    var invalidTabId = invalidTabPane.getAttribute("id");

                    // Find the tab button that corresponds to this tab pane
                    var tabButton = document.querySelector('button[data-bs-target="#' + invalidTabId + '"]');

                    // Trigger a click on the tab button to switch to the first invalid tab
                    if (tabButton) {
                        tabButton.click();
                    }
                }
            }
        });
    }
});
