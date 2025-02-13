const signupForm = document.querySelector(".signup form");
const continueBtn = signupForm.querySelector(".button input");
const errorText = signupForm.querySelector(".error-txt");

signupForm.onsubmit = (e) => {
    e.preventDefault();
};

continueBtn.onclick = () => {
    if (!validateForm()) {
        return;
    }

    const formData = new FormData(signupForm);

    fetch("php/signupProcess.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        if (data === "success") {
            location.href = "index.php";
        } else {
            errorText.style.display = "block";
            errorText.textContent = data;
        }
    })
    .catch(error => {
        console.error("Hiba történt:", error);
    });
};

function validateForm() {
    const usernameField = signupForm.querySelector("input[name='username']");
    const emailField = signupForm.querySelector("input[name='email']");
    const passwordField = signupForm.querySelector("input[name='password']");

    if (!usernameField || !emailField || !passwordField) {
        console.error("Hiba: Hiányzó input mezők!");
        return false;
    }

    const username = usernameField.value.trim();
    const email = emailField.value.trim();
    const password = passwordField.value.trim();

    if (!username || !email || !password) {
        errorText.style.display = "block";
        errorText.textContent = "Minden mezőt ki kell tölteni!";
        return false;
    }

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        errorText.style.display = "block";
        errorText.textContent = "Érvényes e-mail címet adjon meg!";
        return false;
    }

    if (password.length < 6) {
        errorText.style.display = "block";
        errorText.textContent = "A jelszónak legalább 6 karakter hosszúnak kell lennie!";
        return false;
    }

    errorText.style.display = "none";
    return true;
}

