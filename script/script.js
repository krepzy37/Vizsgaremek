// Az elemek kiválasztása
const loginButton = document.getElementById('login');
const loginOverlay = document.getElementById('loginOverlay');
const registerOverlay = document.getElementById('registerOverlay');
const closeOverlayButton = document.getElementById('closeOverlay');
const closeRegisterOverlayButton = document.getElementById('closeRegisterOverlay');
const registerRedirect = document.getElementById('registerRedirect');
const loginRedirect = document.getElementById('loginRedirect');

// Belépés és regisztráció gombok
const loginSubmitButton = loginOverlay.querySelector('button:not(.close)');
const registerSubmitButton = registerOverlay.querySelector('button:not(.close)');

// Login overlay megjelenítése
loginButton.addEventListener('click', () => {
    loginOverlay.style.display = 'flex';
});

// Login overlay bezárása
closeOverlayButton.addEventListener('click', () => {
    loginOverlay.style.display = 'none';
});

// Register overlay megjelenítése
registerRedirect.addEventListener('click', () => {
    loginOverlay.style.display = 'none';
    registerOverlay.style.display = 'flex';
});

// Register overlay bezárása
closeRegisterOverlayButton.addEventListener('click', () => {
    registerOverlay.style.display = 'none';
});

// Login overlay megjelenítése regisztráció után
loginRedirect.addEventListener('click', () => {
    registerOverlay.style.display = 'none';
    loginOverlay.style.display = 'flex';
});

// Mezők ellenőrzése és hibaüzenetek kezelése
function validateInputs(inputs) {
    let isValid = true;

    inputs.forEach(input => {
        const error = input.nextElementSibling; // Hibaüzenet helye
        if (input.value.trim() === '') {
            isValid = false;

            // Ha nincs hibaüzenet, létrehozzuk
            if (!error || !error.classList.contains('error-message')) {
                const errorMessage = document.createElement('div');
                errorMessage.textContent = 'Ez a mező nem lehet üres!';
                errorMessage.classList.add('error-message');
                errorMessage.style.color = 'red';
                errorMessage.style.fontSize = '12px';
                input.after(errorMessage);
            }
        } else if (error && error.classList.contains('error-message')) {
            // Ha van hibaüzenet, de a mezőt kitöltötték, eltávolítjuk
            error.remove();
        }
    });

    return isValid;
}

// Email formátum ellenőrzése
function validateEmail(email) {
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/; // Egyszerű email regex
    return emailPattern.test(email);
}

// Jelszavak egyezésének ellenőrzése
function validatePasswords(password, confirmPassword) {
    if (password !== confirmPassword) {
        return false;
    }
    return true;
}

// Belépés ellenőrzése
loginSubmitButton.addEventListener('click', (e) => {
    e.preventDefault(); // Alapértelmezett működés megakadályozása
    const inputs = loginOverlay.querySelectorAll('input');
    const isValid = validateInputs(inputs);

    if (isValid) {
        alert('Sikeres belépés!');
        loginOverlay.style.display = 'none'; // Opcióként bezárhatjuk az ablakot
    }
});

// Regisztráció ellenőrzése
registerSubmitButton.addEventListener('click', (e) => {
    e.preventDefault(); // Alapértelmezett működés megakadályozása
    const inputs = registerOverlay.querySelectorAll('input');
    const emailInput = registerOverlay.querySelector('input[type="email"]');
    const passwordInput = registerOverlay.querySelector('input[type="password"]:nth-of-type(1)');
    const confirmPasswordInput = registerOverlay.querySelector('input[type="password"]:nth-of-type(2)');

    let isValid = validateInputs(inputs);

    // Email ellenőrzés
    if (!validateEmail(emailInput.value)) {
        isValid = false;
        const error = emailInput.nextElementSibling;
        if (!error || !error.classList.contains('error-message')) {
            const errorMessage = document.createElement('div');
            errorMessage.textContent = 'Kérlek érvényes email címet adj meg!';
            errorMessage.classList.add('error-message');
            errorMessage.style.color = 'red';
            errorMessage.style.fontSize = '12px';
            emailInput.after(errorMessage);
        }
    }

    // Jelszavak egyezésének ellenőrzése
    if (!validatePasswords(passwordInput.value, confirmPasswordInput.value)) {
        isValid = false;
        const error = confirmPasswordInput.nextElementSibling;
        if (!error || !error.classList.contains('error-message')) {
            const errorMessage = document.createElement('div');
            errorMessage.textContent = 'A jelszavak nem egyeznek!';
            errorMessage.classList.add('error-message');
            errorMessage.style.color = 'red';
            errorMessage.style.fontSize = '12px';
            confirmPasswordInput.after(errorMessage);
        }
    }

    if (isValid) {
        alert('Sikeres regisztráció!');
        registerOverlay.style.display = 'none'; // Opcióként bezárhatjuk az ablakot
    }
});
