<!DOCTYPE html>
<html lang="hu" ng-app="carApp">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Főoldal</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/cursor.css">
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.8.2/angular.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

</head>

<?php 
session_start();
    //include "./kisegitok/head.html";
    include "./kisegitok/nav.php";

    

?>
<div class="szoveg" style="color: white;">
    <h3>Üdvözöljük a Cartalk weboldalán! </h3>
    <h5>A weboldal célja, hogy segítséget nyújtson az autó tulajdonosoknak. 
        Lehetősége van bejegyzéseket létrehozni, ahol a közösség tagjaitól segítséget kérhet az autójával 
        kapcsolatos kérdéseire vagy éppen segítséget nyújthat a közösség másik tagjainak a problémájuk 
        megoldásában. Igyekeztünk a weboldalt a lehető leg felhasználó barátabbá tenni, csoportosítottuk 
        a márkák alapján a modelleket a könnyebb megtalálás érdekében. Biztosítunk lehetőséget privát 
        társalgásra egymás között, így nem szükséges publikusan beszélgetni. </h5>
    <h6>Amennyiben felmerülne kérdése, kérjük lépjen velünk kapcsolatba a megadott elérhetőségeinken keresztül.</h6>
    <section
  class="relative group flex flex-col items-center justify-center w-full h-full"
>
  <div
    class="file relative w-60 h-40 cursor-pointer origin-bottom [perspective:1500px] z-50"
  >
    <div
      class="work-5 bg-amber-600 w-full h-full origin-top rounded-2xl rounded-tl-none group-hover:shadow-[0_20px_40px_rgba(0,0,0,.2)] transition-all ease duration-300 relative after:absolute after:content-[''] after:bottom-[99%] after:left-0 after:w-20 after:h-4 after:bg-amber-600 after:rounded-t-2xl before:absolute before:content-[''] before:-top-[15px] before:left-[75.5px] before:w-4 before:h-4 before:bg-amber-600 before:[clip-path:polygon(0_35%,0%_100%,50%_100%);]"
    ></div>
    <div
      class="work-4 absolute inset-1 bg-zinc-400 rounded-2xl transition-all ease duration-300 origin-bottom select-none group-hover:[transform:rotateX(-20deg)]"
    ></div>
    <div
      class="work-3 absolute inset-1 bg-zinc-300 rounded-2xl transition-all ease duration-300 origin-bottom group-hover:[transform:rotateX(-30deg)]"
    ></div>
    <div
      class="work-2 absolute inset-1 bg-zinc-200 rounded-2xl transition-all ease duration-300 origin-bottom group-hover:[transform:rotateX(-38deg)]"
    ></div>
    <div
      class="work-1 absolute bottom-0 bg-gradient-to-t from-amber-500 to-amber-400 w-full h-[156px] rounded-2xl rounded-tr-none after:absolute after:content-[''] after:bottom-[99%] after:right-0 after:w-[146px] after:h-[16px] after:bg-amber-400 after:rounded-t-2xl before:absolute before:content-[''] before:-top-[10px] before:right-[142px] before:size-3 before:bg-amber-400 before:[clip-path:polygon(100%_14%,50%_100%,100%_100%);] transition-all ease duration-300 origin-bottom flex items-end group-hover:shadow-[inset_0_20px_40px_#fbbf24,_inset_0_-20px_40px_#d97706] group-hover:[transform:rotateX(-46deg)_translateY(1px)]"
    ></div>
  </div>
  <p class="text-3xl pt-4 opacity-20">Hover over</p>
</section>
</div>


<?php 
include "./kisegitok/footer.php";
    include "./kisegitok/end.html";

?>
