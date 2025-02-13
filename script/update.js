// Elemek kiválasztása a DOM-ból
const form = document.querySelector(".signup form");
const continueBtn = form.querySelector(".button input");
const errorText = document.querySelector(".error-txt");

form.onsubmit = (e)=>{
    e.preventDefault();
}

continueBtn.onclick = ()=>{
//Ajax
let xhr = new XMLHttpRequest();
xhr.open("POST", "php/updateProcess.php", true);
xhr.onload = ()=>{
    if(xhr.readyState === XMLHttpRequest.DONE){
        if(xhr.status === 200){
            let data = xhr.response;
            //console.log(data);
            if(data == "success"){
                location.href = "profile.php"
            }else{
                errorText.textContent = data;
                errorText.style.display = "block";               
            }
        }
    }
}

//az adatokat el kell küldenünk az ajaxon keresztül a PHP-nek
let formData = new FormData(form);
xhr.send(formData);
}

