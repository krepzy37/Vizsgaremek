<?php 
    include "./kisegitok/head.html";
    include "./kisegitok/nav.php";
    

?>
<h1>Kapcsolat</h1>

<div>
    <h2>Vegye fel felüvk a kapcsolatot</h2>
    <p>Lorem ipsum, dolor sit amet consectetur adipisicing elit. Accusantium fugiat ullam assumenda asperiores facilis. Ullam eligendi dolore similique cupiditate rem ipsam culpa nesciunt libero eius obcaecati, consequatur voluptate! Quia provident quidem explicabo, repellat saepe optio sapiente consectetur officiis vero cum velit quas perspiciatis magnam nostrum reiciendis dignissimos voluptatibus, culpa ipsum!</p>
    <div>
        <h4>Email címünk:</h4>
        <div class="copy-container">
                📧 Email: <span id="email">asd4@gmail.com</span>
                <button onclick="copyToClipboard('email')">Másolás</button>
            </div>
        <p>Az emailekre munkanapokon 48 órán belül válaszolunk.</p>
    </div>
</div>


<script>
        function copyToClipboard(id) {
            const text = document.getElementById(id).innerText;
            navigator.clipboard.writeText(text).then(() => {
                alert('Szöveg másolva: ' + text);
            }).catch(err => {
                alert('Hiba történt a másoláskor: ' + err);
            });
        }
    </script>
<?php 
    include "./kisegitok/end.html";

?>
