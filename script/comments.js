// Hozzászólások megjelenítése/elrejtése gombbal
document.querySelectorAll('.toggle-comments').forEach(button => {
    button.addEventListener('click', function() {
        let postId = this.getAttribute('data-post-id');
        let commentsContainer = document.getElementById('comments-' + postId);

        if (commentsContainer.style.display === 'none') {
            commentsContainer.style.display = 'block';
            this.textContent = 'Hozzászólások elrejtése';
        } else {
            commentsContainer.style.display = 'none';
            this.textContent = 'Hozzászólások megjelenítése';
        }
    });
});

// Hozzászólás hozzáadása AJAX segítségével
document.querySelectorAll('.comment-form').forEach(form => {
    form.addEventListener('submit', function(event) {
        event.preventDefault();
        let postId = this.getAttribute('data-post-id');
        let commentText = this.querySelector('textarea').value;
        let commentImage = this.querySelector('input[name="comment_image"]').files[0];

        let formData = new FormData();
        formData.append('comment_text', commentText);
        formData.append('post_id', postId);
        if (commentImage) formData.append('comment_image', commentImage); // Kép hozzáadása

        fetch("php/add-comment.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    location.reload(); // Oldal frissítése a sikeres komment után
                }
            })
            .catch(error => console.error("Hiba:", error));
    });
});