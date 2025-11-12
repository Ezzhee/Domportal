<?php
include 'db.php'; // подключение к базе
session_start();
?>
<!DOCTYPE HTML>
<html>
  <head>
    <title>Новости ЖКХ Астаны</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
    <link rel="stylesheet" href="assets/css/main.css" />
  </head>
  <body>
    <div id="page-wrapper">

      <!-- Header -->
      <div id="header-wrapper">
        <div class="container">
          <div class="row">
            <div class="col-12">
              <header id="header">
                <h1><a href="index.php" id="logo">Новости ЖКХ Астаны</a></h1>
                <nav id="nav">
                  <a href="index.php" class="current-page-item">Главная</a>
                  <a href="twocolumn1.php">Новости</a>
                  <a href="twocolumn2.php">Статьи</a>
                  <a href="onecolumn.php">Форум</a>
                  <a href="threecolumn.php">Полезное</a>
                </nav>
              </header>
            </div>
          </div>
        </div>
      </div>

      <!-- Banner -->
      <div id="banner-wrapper">
        <div class="container">
          <div id="banner">
            <h2>Добро пожаловать на портал ЖКХ</h2>
            <span>Все новости, объявления и обсуждения в одном месте</span>
          </div>
        </div>
      </div>

      <!-- Main -->
      <div id="main">
        <div class="container">
          <div class="row main-row">
            <div class="col-12">
              <section>
                <h2>Последние новости</h2>
                <?php
                // Выводим последние новости из БД
                $result = $conn->query("SELECT * FROM posts ORDER BY created_at DESC LIMIT 5");
                if($result->num_rows > 0){
                  while($row = $result->fetch_assoc()){
                    echo "<article>";
                    echo "<h3>{$row['title']}</h3>";
                    echo "<p>{$row['content']}</p>";
                    echo "<small>Автор: {$row['author']} | {$row['created_at']}</small>";
                    echo "<hr>";
                    echo "</article>";
                  }
                } else {
                  echo "<p>Новостей пока нет.</p>";
                }
                ?>
                <footer class="controls">
                  <a href="twocolumn1.php" class="button">Все новости</a>
                </footer>
              </section>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div id="footer-wrapper">
        <div class="container">
          <div class="row">
            <div class="col-12">
              <div id="copyright">
                &copy; 2025 ЖКХ Портал. Дизайн: <a href="http://html5up.net">HTML5 UP</a>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>

    <!-- Scripts -->
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/browser.min.js"></script>
    <script src="assets/js/breakpoints.min.js"></script>
    <script src="assets/js/util.js"></script>
    <script src="assets/js/main.js"></script>

  </body>
</html>