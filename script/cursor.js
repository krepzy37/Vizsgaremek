const cursor = document.querySelector('.custom-cursor');
const follower = document.querySelector('.cursor-follower');

// A kurzor és követő pozíciók
let mouseX = 0, mouseY = 0;
let cursorX = 0, cursorY = 0;

// Az egér mozgás követése
document.addEventListener('mousemove', (e) => {
  mouseX = e.clientX;
  mouseY = e.clientY;

  // A nagy kör (follower) pontosan az egér pozíciójához igazodik
  follower.style.top = `${mouseY}px`;
  follower.style.left = `${mouseX}px`;
});

// Animációs hurok a kisebb kör késleltetett mozgásához
function animate() {
  cursorX += (mouseX - cursorX) * 0.1; // Késleltetett mozgás X
  cursorY += (mouseY - cursorY) * 0.1; // Késleltetett mozgás Y

  // A kisebb kör (cursor) a nagy kör aktuális pozíciójához igazodik
  cursor.style.top = `${cursorY}px`;
  cursor.style.left = `${cursorX}px`;

  requestAnimationFrame(animate); // Animáció folytatása
}

// Az animáció elindítása
animate();

// Gombok fölötti interakciók kezelése
document.querySelectorAll('a, button, input').forEach((el) => {
  el.addEventListener('mouseenter', () => {
    // Körök eltüntetése
    cursor.style.opacity = '0';
    follower.style.opacity = '0';
  });

  el.addEventListener('mouseleave', () => {
    // Körök visszaállítása
    cursor.style.opacity = '1';
    follower.style.opacity = '1';
  });
});
