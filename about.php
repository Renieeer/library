<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'inc/header.php'; ?>

  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>About | Library Management System</title>
  <link rel="stylesheet" href="style.css">
  <style>
    body {
      font-family: "Georgia", serif;
      background-color: #fffaf5;
      color: #2e2e2e;
      margin: 0;
      padding: 0;
    }

    header {
      padding: 20px 60px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: #fff;
      border-bottom: 1px solid #eee;
    }

    header a {
      text-decoration: none;
      color: #a97447;
      margin-left: 25px;
      font-weight: 500;
    }

    .about-section {
      display: flex;
      justify-content: center;
      align-items: center;
      flex-direction: column;
      text-align: center;
      padding: 100px 20px;
      background-color: #fffaf5;
    }

    .about-section h1 {
      font-size: 48px;
      margin-bottom: 20px;
      font-weight: 700;
    }

    .about-section p {
      font-size: 18px;
      max-width: 800px;
      line-height: 1.7;
      margin-bottom: 40px;
    }

    .mission-vision {
      background: #fff;
      padding: 60px 20px;
      text-align: center;
    }

    .mission-vision h2 {
      font-size: 32px;
      margin-bottom: 20px;
    }

    .mission-vision p {
      max-width: 700px;
      margin: 0 auto 40px;
      font-size: 18px;
      line-height: 1.6;
    }

    footer {
      background-color: #fff;
      text-align: center;
      padding: 30px 10px;
      border-top: 1px solid #eee;
      color: #a97447;
    }
  </style>
</head>
<body>

  <header>
    <div class="logo"><strong>Library System</strong></div>
    <nav>
      <a href="index.php">Home</a>
      <a href="about.php">About</a>
      <a href="allbook.php">Articles</a>
      <a href="log_in.php">Account</a>
    </nav>
  </header>

  <section class="about-section">
    <h1>About Our Library</h1>
    <p>
      Our Library Management System is designed to make reading and borrowing books easier, faster, and more enjoyable for everyone. 
      We believe that access to knowledge should be simple and seamless, empowering readers to learn and explore anywhere, anytime.
    </p>
  </section>

  <section class="mission-vision">
    <h2>Our Mission</h2>
    <p>
      To promote the love of reading and lifelong learning by providing a modern and accessible digital platform that connects readers with books effortlessly.
    </p>

    <h2>Our Vision</h2>
    <p>
      To become a leading digital library that fosters education, creativity, and literacy through innovation and technology.
    </p>
  </section>

  <footer>
    &copy; 2025 Library Management System | Designed with ❤️ for readers
  </footer>

</body>
</html>
