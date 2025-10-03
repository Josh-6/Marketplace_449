const passwordInput = document.querySelector("#psw");
const repeatPasswordInput = document.querySelector("#psw-repeat"); 
const showPasswordCheckbox = document.querySelector("#showPassword");

// Password toggle
showPasswordCheckbox.addEventListener("change", function () {
    if (this.checked) {
        // Show passwords
        passwordInput.type = "text";
        if (repeatPasswordInput) repeatPasswordInput.type = "text"; 
    } else {
        // Hide passwords
        passwordInput.type = "password";
        if (repeatPasswordInput) repeatPasswordInput.type = "password";
    }
});
