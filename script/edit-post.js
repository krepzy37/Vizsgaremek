document.addEventListener("DOMContentLoaded", function() {
    let modal = document.getElementById("editModal");
    let closeModal = document.querySelector(".close");

    // Szerkesztés gombok eseménykezelése
    document.querySelectorAll(".edit-post-btn").forEach(button => {
        button.addEventListener("click", function() {
            let postId = this.getAttribute("data-id");
            let title = this.getAttribute("data-title");
            let body = this.getAttribute("data-body");
            let image = this.getAttribute("data-image");

            // Betöltjük az adatokat az űrlapba
            document.getElementById("edit_post_id").value = postId;
            document.getElementById("edit_title").value = title;
            document.getElementById("edit_body").value = body;

            let imgElem = document.getElementById("current_post_image");
            if (image) {
                imgElem.src = image;
                imgElem.style.display = "block";
            } else {
                imgElem.style.display = "none";
            }

            modal.style.display = "block"; // Modal megjelenítése
        });
    });

    // Modal bezárása
    closeModal.addEventListener("click", function() {
        modal.style.display = "none";
    });

    // AJAX-al frissítjük a posztot
    document.getElementById("editPostForm").addEventListener("submit", function(event) {
        event.preventDefault();

        let formData = new FormData(this);
        formData.append('remove_image', document.getElementById('remove_image').checked ? 'true' : 'false');

        fetch("php/update-post.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    location.reload(); // Sikeres módosítás után frissítés
                }
            })
            .catch(error => console.error("Hiba:", error));
    });
});