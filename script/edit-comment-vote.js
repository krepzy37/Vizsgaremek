document.addEventListener("DOMContentLoaded", function() {
    let modal = document.getElementById("editCommentModal");
    let closeModal = document.querySelector("#editCommentModal .close");
    let editForm = document.getElementById("editCommentForm");

    document.querySelectorAll(".edit-comment-btn").forEach(button => {
        button.addEventListener("click", function() {
            let commentId = this.getAttribute("data-id");
            let text = this.getAttribute("data-text");
            let image = this.getAttribute("data-image");

            document.getElementById("edit_comment_id").value = commentId;
            document.getElementById("edit_comment_text").value = text;

            let imgElem = document.getElementById("current_comment_image");
            let deleteImageContainer = document.getElementById("delete_image_container");
            let deleteImageCheckbox = document.getElementById("delete_comment_image");

            if (image) {
                imgElem.src = image;
                imgElem.style.display = "block";
                deleteImageContainer.style.display = "block"; // Megjeleníti a törlés checkboxot
            } else {
                imgElem.style.display = "none";
                deleteImageContainer.style.display = "none"; // Elrejti a törlés checkboxot
            }

            deleteImageCheckbox.checked = false; // Alapértelmezett érték

            modal.style.display = "block";
        });
    });

    closeModal.addEventListener("click", function() {
        modal.style.display = "none";
    });

    window.addEventListener("click", function(event) {
        if (event.target === modal) {
            modal.style.display = "none";
        }
    });

    // AJAX beküldés
    editForm.addEventListener("submit", function(event) {
        event.preventDefault();

        let formData = new FormData(editForm);
        fetch("php/update-comment.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    modal.style.display = "none";
                    location.reload();
                }
            })
            .catch(error => console.error("Hiba:", error));
    });
});








document.addEventListener("DOMContentLoaded", function() {
    const voteButtons = document.querySelectorAll(".upvote, .downvote");

    voteButtons.forEach(button => {
        button.addEventListener("click", function() {
            const id = this.getAttribute("data-id"); // data-id tartalmazza a poszt vagy komment ID-ját
            const voteType = this.classList.contains("upvote") ? "upvote" : "downvote";
            const type = this.closest('.vote-buttons').parentElement.classList.contains('comments') ? 'comment' : 'post'; // Determine if it's a comment or post

            fetch("php/vote.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: `id=${id}&vote_type=${voteType}&type=${type}` // Send the correct type
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message); // Display the message
                    if (data.success) {
                        // Update the score dynamically
                        updateScore(id, type);
                        updateVoteButtons(id, type, voteType); // Update the vote buttons accordingly
                    }
                })
                .catch(error => console.error("Hálózati hiba: ", error));
        });
    });
});

function updateVoteButtons(id, type, voteType) {
    const upvoteButton = document.querySelector(`.upvote[data-id='${id}']`);
    const downvoteButton = document.querySelector(`.downvote[data-id='${id}']`);

    // Reset button states
    upvoteButton.classList.remove('voted');
    downvoteButton.classList.remove('voted');

    // Add the voted class based on the vote type
    if (voteType === 'upvote') {
        upvoteButton.classList.add('voted');
    } else {
        downvoteButton.classList.add('voted');
    }
}

// Fetch user vote status on page load

voteButtons.forEach(button => {
    const id = button.getAttribute("data-id");
    const type = button.classList.contains("upvote") ? "post" : "comment";

    fetch("php/get-user-vote.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: `id=${id}&type=${type}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.vote_type === 'upvote') {
                button.classList.add('voted');
            } else if (data.vote_type === 'downvote') {
                button.classList.add('voted');
            }
        }
    })
    .catch(error => console.error("Hálózati hiba: ", error));
});

// Function to update the score dynamically
function updateScore(id, type) {
    const voteQuery = type === 'post' ? `SELECT SUM(CASE WHEN vote_type = 'upvote' THEN 1 ELSE -1 END) as score FROM votes WHERE post_id = ?` : `SELECT SUM(CASE WHEN vote_type = 'upvote' THEN 1 ELSE -1 END) as score FROM votes WHERE comment_id = ?`;

    fetch("php/get-vote_score.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: `id=${id}&type=${type}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const scoreElement = document.getElementById(type === 'post' ? `post-score-${id}` : `comment-score-${id}`);
                scoreElement.textContent = data.score; // Update the score display
            }
        })
        .catch(error => console.error("Hálózati hiba: ", error));
}

