<?php
require "classes/Database.php";
require "classes/Produkt.php";
require "classes/Sprava.php";
require "classes/Cache.php";
require "classes/Auth.php";

session_start();
if (empty($_SESSION["kosik"])) {
    $_SESSION["kosik"] = Auth::generateRandomCode();
}
$database   = new Database();
$connection = $database->connectionDB();

// Určíme stránku
$page            = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$articlesPerPage = 5;
$offset          = ($page - 1) * $articlesPerPage;

// 1) Kešujeme právě tuto stránku produktů
$prods_key = "products_page_{$page}";
$produkt = Cache::get($prods_key, 86400);
if ($produkt === false) {
    $produkt = Produkt::getALLProdukts($connection, $articlesPerPage, $offset);
    Cache::put($prods_key, $produkt);
}

// 2) Kešujeme celkový počet produktů pro stránkování
$total_key    = 'products_total_count';
$totalArticles = Cache::get($total_key, 86400);
if ($totalArticles === false) {
    $totalArticles = Produkt::getTotalCount($connection);
    Cache::put($total_key, $totalArticles);
}
$totalPages = ceil($totalArticles / $articlesPerPage);

// 3) Kešujeme kategorie
$cats_key = 'all_categories';
$kategorie = Cache::get($cats_key, 86400);
if ($kategorie === false) {
    $kategorie = Produkt::Allcategories($connection);
    Cache::put($cats_key, $kategorie);
}

// 4) Kešujeme blogové zprávy
$sprava_key = 'all_sprava';
$sprava     = Cache::get($sprava_key, 86400);
if ($sprava === false) {
    $sprava = Sprava::getALLSprava($connection);
    Cache::put($sprava_key, $sprava);
}
session_write_close();
?>
<!DOCTYPE html>
    <html lang="cs">
    <head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <!-- zrychlí navázání spojení na CDN/font-awesome -->
  <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
<link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">

<!-- zrychlí stažení CSS a JS -->
<link rel="preload" href="css/produkty-all.css" as="style">
<link rel="preload" href="JavaScript/all.js" as="script">

  <?php require "assets/SEO.php" ?>

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">

  <!-- CSS soubory -->
  <link rel="stylesheet" href="css/produkty-all.css">
</head>
   
    <body>

    <?php require "assets/header.php" ?>


        <main>
        <div class="sidebar">
        <div class="velde">
        <h2>Kategorie produktů</h2></div>
        
        <ul>
        <?php foreach ($kategorie as $index): ?>
            <li><a href="/one-categorie.php?kategorie_type=<?=$index["kategorie_type"]?>&kategorie_type2=<?=$index["kategorie_type2"]?>&kategorie_name=<?=$index["kategorie_name"]?>"><?=$index["kategorie_name"]?></a></li>
            <?php endforeach?>
        </ul>
        <div class="contact">
        <h1>Blog - Lysun</h1>
        <?php foreach ($sprava as $index): ?>
          <a href="/one-sprava.php?id=<?=$index['id']?>"><img src="images/image-spravy/<?= htmlspecialchars ($index["sprava_img"])?>" alt="blog Lysun" loading="lazy"  decoding="async">
          <?= htmlspecialchars($index["sprava_name"]) ?></a>
        <?php endforeach?>
        </div>
       
        
    </div>

   
    <div class="cart">
  <a href="/kosik.php">
    <i class="fas fa-cart-arrow-down"></i>
  </a>
</div>
    <div class="course">
      <!-- Alert box -->
<div id="custom-alert" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); text-align: center; z-index: 1000; width:250px;">
    <img src="https://img.icons8.com/color/96/000000/shopping-cart.png" alt="Košík" style="width: 48px; height: 48px; font-size: 18px">
    <h3 style="margin: 10px 0; font-size: 18px">Produkt byl přidán do košíku!</h3>
    <img src="https://img.icons8.com/color/48/000000/checkmark.png" alt="Fajfka" style="width: 48px; height: 48px; font-size: 18px">
</div>

<!-- Overlay -->
<div id="custom-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.4); z-index: 999;"></div>

    <div class="retro">
        <div class="velde">
       <h2> <i class="fas fa-caret-right"></i> Kategorie produktů</h2>
        <i class="fas fa-arrow-circle-down"></i></div>
       <div class="acka"> 
        <?php foreach ($kategorie as $index): ?>
            <a href="/one-categorie.php?kategorie_type=<?=$index["kategorie_type"]?>&kategorie_type2=<?=$index["kategorie_type2"]?>&kategorie_name=<?=$index["kategorie_name"]?>"><i class="fas fas fa-angle-right"></i> <?=$index["kategorie_name"]?></a>
            <?php endforeach?>
       </div>
       
    </div>
      <h1><i class="fas fa-caret-right"></i> Všechny produkty Lysun</h1>
    <div class="box-container">


       <?php foreach ($produkt as $index): ?>
         
       
        <a href="/one-produkt.php?id=<?=$index['produkt_id']?>"><div class="box">
       
        <div class="image">
        <img src="images/produkt-img/<?php echo htmlspecialchars ($index["produkt_image1"])?>" alt="Lysun products" loading="lazy"  decoding="async">
        </div>
       
     
                 
              <div class="kosik">
              <h2><?php echo htmlspecialchars($index["produkt_name"]) ?></h2>
              <h4><?=$index["produkt_description"] ?></h4>
              
              
              <div class="spolecne">
              
              

              <p ><?=$index["produkt_price_text"] ?></p>

              <?php if ($index["stav"] ==="Na sklade"): ?>
              
              <form class="form" onsubmit="addToCart(event, <?=$index['produkt_id']?>)">
    <button type="submit"> 
        <span>Přidat do košíku</span> 
        <i class="fas fa-cart-arrow-down"></i>
    </button>
</form>

<?php else: ?>
           <p style="font-style: italic;"> <?=$index['stav']?> </p>
            <?php endif ?>

</div>

              </div>
              </div></a>
          <?php endforeach?>
         
        </div>
        
       
        </main>
        <div class="fromtop">
        <button onclick="scrollToTop()" id="backToTop"><i class="fas fa-chevron-up"></i></button></div>
        </div>
         <!-- Stránkování -->
         <?php if ($totalArticles>$articlesPerPage): ?>
         <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>">&laquo; Předchozí</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?= $i ?>" class="<?= ($i == $page) ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?>">Další &raquo;</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <?php require "assets/footer.php"; ?>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js" defer></script>

<script src="JavaScript/all.js" defer></script>


<script>
  document.addEventListener("DOMContentLoaded", function () {
    const toggleButton = document.querySelector(".retro .velde");
    const links = document.querySelectorAll(".retro a");

    // Ensure links are initially hidden
    links.forEach((link) => {
      link.style.display = "none";
    });

    toggleButton.addEventListener("click", () => {
      links.forEach((link) => {
        link.style.display = link.style.display === "block" ? "none" : "block";
      });
    });
  });
</script>

<script>
function addToCart(event, productId) {
    // Zamezíme standardnímu odeslání formuláře
    event.preventDefault();

    // Pošleme asynchronní požadavek na server
    fetch(`admin/insert-kosik.php?id=${productId}&web=produkty`, {
        method: 'POST'
    })
    .then(response => {
        if (response.ok) {
            return response.json(); // Očekáváme JSON odpověď
        } else {
            throw new Error('Nepodařilo se přidat produkt do košíku.');
        }
    })
    .then(data => {
        if (data.success) {
            // Zobrazíme vlastní alert
            showCustomAlert();
        } else {
            alert(data.error || 'Došlo k chybě.');
        }
    })
    .catch(error => {
        alert('Chyba: ' + error.message);
    });
}

// Funkce pro zobrazení vlastního alertu
function showCustomAlert() {
    const alertBox = document.getElementById('custom-alert');
    const overlay = document.getElementById('custom-overlay');

    alertBox.style.display = 'block';
    overlay.style.display = 'block';

    setTimeout(() => {
        alertBox.style.display = 'none';
        overlay.style.display = 'none';
    }, 3000); // 3 sekundy
}

</script>
    </body>
    </html>