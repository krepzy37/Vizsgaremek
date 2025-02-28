document.querySelector('.toggle-post-form').addEventListener('click', function() {
    let postForm = document.getElementById('postForm');
    if (postForm.style.display === 'none') {
        postForm.style.display = 'block';
        this.textContent = '🙅‍♂️ Mégsem';
    } else {
        postForm.style.display = 'none';
        this.textContent = '✍️ Poszt írása';
    }
});