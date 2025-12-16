<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="/css/styleDashboardAdmin.css">
  <style>
    .profile-image-placeholder {
      width: 100px;
      height: 100px;
      background-color: #e9e9e9;
      /* Very light gray */
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      border: 3px solid white;
      /* Border color */
      box-shadow: 0 0 0 2px #ccc;
      /* Ring effect */
    }

    .custom-align {
      display: block;
      position: relative;
    }

    .custom-align p {
      position: absolute;
      top: 50%;
      left: 15px;
      transform: translateY(-50%);
    }
  </style>
</head>

<body>
  <aside class="sidebar" style="width: 250px;">
    <div class="d-flex flex-column align-items-center">
      <div class="sidebar-box box-blue custom-align" style="width: 200px; padding: 25px; position: relative; border-radius: 5px;" title="Home" onclick="window.location.href = '/'">
        <p style="text-align: left;"><i class="fas fa-tachometer-alt me-2"></i>Home</p>
      </div>
      <div class="sidebar-box box-blue custom-align" style="width: 200px; padding: 25px; position: relative; border-radius: 5px;" title="Profile" onclick="window.location.href = '/updateProfile'">
        <p style="text-align: left; line-height: 5px;">Profile Settings</p>
      </div>
      <div class="sidebar-box box-blue custom-align" style="width: 200px; padding: 25px; position: relative; border-radius: 5px;" title="Profile" onclick="window.location.href = '/jobseed'">
        <p style="text-align: left;"><i class="fas fa-database me-2"></i>Job Seeder Tool</p>
      </div>
    </div>
    <div class="mt-auto">
      <div class="sidebar-box box-blue" style="background-color: gray; width: 200px; padding: 25px; position: relative; border-radius: 5px;" title="Logout" id="btn-logout">
        <p style="text-align: left; line-height: 5px;">Logout</p>
      </div>
    </div>
  </aside>

  <div class="main-wrapper" style="width: calc(100vw - 250px); transform: translateX(150px);">

    <div class="d-flex justify-content-between align-items-center mb-5">
      <h1 class="header-title m-0">Dashboard - Admin</h1>
      <div class="d-flex align-items-center gap-3 profile-section">
        <div class="profile-image-placeholder position-relative" id="profile-image-placeholder" style="margin-right: 15px;">
          <img id="current-avatar" src="" alt="Avatar" class="rounded-circle w-100 h-100 object-fit-cover" style="display: none;">
          <svg class="profile-icon me-3" id="photo-placeholder-icon" fill="currentColor" viewBox="0 0 16 16">
            <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0" />
            <path d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.758 1.226 5.468 2.37A7 7 0 0 0 8 1" />
          </svg>
        </div>

        <div class="d-flex flex-column">
          <span class="fw-bold" style="font-size: 1.1rem;"><?= $username ?></span>
          <small style="color: #666;">@<?= (empty($placeholder) ? $username : $placeholder) ?></small>
        </div>
      </div>
    </div>

    <div class="row gx-5">
      <div class="col-lg-8">
        <div class="row g-4 mb-4 horizontal-scroll" id="job-row">
        </div>

        <div class="application-section">
          <div class="section-header">
            <h2 class="fw-bold fs-4 m-0">Users</h2>
          </div>

          <div class="row table-head mx-0">
            <div class="col-5 ps-0">Username</div>
            <div class="col-5">Role</div>
            <div class="col-2 text-end pe-0">Action</div>
          </div>

          <div id="user-list"></div>
        </div>

      </div>

      <div class="col-lg-4">
        <div class="right-widget-container">

          <h3 class="widget-title">Admin Statistics</h3>
          <div id="admin-stats">
            <div class="col-lg-4">


              <div class="right-widget-container">
                <h3 class="widget-title">Admin Statistics</h3>
                <div id="admin-stats" class="stats-list">
                  <div class="text-muted">Loading...</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
      <script src="/js/scriptDashboardAdmin.js"></script>
</body>

</html>
