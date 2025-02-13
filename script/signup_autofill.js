document.addEventListener("DOMContentLoaded", () => {
    // Automatikusan kitöltendő adatok
    const formData = {
        username: "jegestea",        
        email: "koczka.balazs1201@gmail.com",
        password: "titkosjelszo",
        
    };

    // Mezők kitöltése
    document.querySelector("input[name='username']").value = formData.username;
    document.querySelector("input[name='email']").value = formData.email;
    document.querySelector("input[name='password']").value = formData.password;
});