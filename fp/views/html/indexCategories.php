<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ConnectIn</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="../css/styleIndex.css" rel="stylesheet">
</head>

<body>

  <div class="main-layout d-flex">
    <aside id="sidebar" class="sidebar d-flex flex-column">
      <div class="p-3 user-profile">
        <div class="d-flex align-items-center mb-4">
          <a href="./testpage.html">
            <svg class="profile-icon me-3" fill="currentColor" viewBox="0 0 16 16">
              <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0" />
              <path d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.758 1.226 5.468 2.37A7 7 0 0 0 8 1" />
            </svg>
          </a>

          <div>
            <a href="./testpage.html">
              <h5 class="m-0"><?= $username ?></h5>
            </a>
            <p class="m-0 text-secondary small">@<?= (empty($placeholder) ? $username : $placeholder) ?></p>
          </div>
        </div>
      </div>

      <nav class="nav-links flex-grow-1">
        <a href="/" class="nav-link">Home</a>
      </nav>

      <div class="bottom-bar p-3 mt-auto">
        <a href="./testpage.html" class="d-block text-white text-decoration-none py-1">Account Settings</a>
        <a href="./login.html" class="d-block text-white text-decoration-none py-1">Log Out</a>
      </div>
    </aside>

    <main class="content flex-grow-1">

      <div class="search-bar">
        <input type="text" class="form-control search-input" placeholder="Search">
      </div>

      <div id="post-feed" class="post-feed p-3">
      </div>

      <button id="post-button" class="btn post-btn position-fixed">
        Post +
      </button>

    </main>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../js/scriptIndex.js"></script>
</body>

</html>
