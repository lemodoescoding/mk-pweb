<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Profile - ConnectIn</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="../css/styleIndex.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

  <style>
    /* Define custom variables if they are not in styleIndex.css */
    :root {
      --sidebar-width: 90px;
      --blue-primary: #3b82f6;
      --content-bg: #f0f0f0;
      /* Light gray background */
      --input-bg: #d3d3d3;
      /* Darker gray for the input fields */
    }

    /* Ensure layout structure matches previous settings */
    html,
    body {
      height: 100%;
      margin: 0;
    }

    .main-layout {
      min-height: 100vh;
    }

    /* The main content area, shifted by the sidebar width */
    .content {
      background-color: var(--content-bg);
      margin-left: var(--sidebar-width);
      min-height: 100vh;
      flex-grow: 1;
      width: calc(100% - var(--sidebar-width));
      padding-top: 30px;
      /* Space from the top */
    }

    /* --- Custom Styles for Profile Card --- */
    .profile-card-container {
      max-width: 800px;
      /* Limit width for centered look */
      margin: 0 auto;
      background-color: white;
      padding: 30px;
      border-radius: 8px;
      /* Slight rounding for the card */
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

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

    .edit-input-group {
      background-color: var(--content-bg);
      /* Use the light gray for fields */
      border-radius: 5px;
      height: 55px;
      margin-bottom: 25px;
      padding: 0 15px;
    }

    .edit-label {
      color: #6c757d;
      /* Muted gray text for labels */
      font-weight: 500;
    }

    .edit-icon {
      color: #6c757d;
      /* Muted gray for the pencil icon */
    }

    .save-btn {
      background-color: var(--blue-primary);
      color: white;
      padding: 10px 40px;
      border-radius: 50px;
      border: none;
      font-weight: bold;
    }

    /* Sidebar box styles (copied from previous context for completeness) */
    .sidebar {
      width: var(--sidebar-width);
      background-color: #E0E0E0;
      position: fixed;
      top: 0;
      bottom: 0;
      left: 0;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding-top: 30px;
      padding-bottom: 30px;
      z-index: 100;
    }

    .sidebar-box {
      width: 55px;
      height: 55px;
      margin-bottom: 20px;
      border-radius: 0;
      cursor: pointer;
      text-decoration: none;
      font-size: 0.6rem;
      font-weight: bold;
      text-align: center;
    }

    .box-blue {
      background-color: var(--blue-primary);
    }

    .box-gray {
      background-color: #a0a0a0;
    }

    .text-white {
      color: white !important;
    }
  </style>
</head>

<body>

  <div class="main-layout d-flex">
    <aside class="sidebar" style="width: 200px;">
      <div class="d-flex flex-column align-items-center w-100">
        <a class="sidebar-box box-gray d-flex align-items-center justify-content-center text-white" style="width: 150px; font-size: 1rem;" title="Home" href="/">Home</a>
        <?php if ($role === 'admin'): ?>
          <a class="sidebar-box box-blue d-flex align-items-center justify-content-center text-white" title="/admin" style="width: 150px; font-size: 1rem;" onclick="window.location.href = '/admin'">Dashboard</a>
        <?php else: ?>
          <a class="sidebar-box box-blue d-flex align-items-center justify-content-center text-white" title="/dashboard" style="width: 150px; font-size: 1rem;" onclick="window.location.href = '/dashboard'">Dashboard</a>
        <?php endif; ?>
      </div>
    </aside>

    <main class="content flex-grow-1">
      <div class="profile-card-container">

        <h3 class="fw-bold mb-4">Edit Profile</h3>

        <form id="avatar-form" enctype="multipart/form-data">
          <div class="d-flex justify-content-center mb-5 position-relative">
            <div class="profile-image-placeholder position-relative" id="profile-image-placeholder">
              <img id="current-avatar" src="" alt="Avatar" class="rounded-circle w-100 h-100 object-fit-cover" style="display: none;">
              <i id="placeholder-icon" class="fa-solid fa-user-circle fa-2xl" style="color: #6c757d;"></i>
              <div id="avatar-edit-overlay" class="position-absolute w-100 h-100 rounded-circle d-flex align-items-center justify-content-center"
                style="background-color: rgba(0, 0, 0, 0.4); opacity: 0; transition: opacity 0.2s; cursor: pointer;">
                <i class="fa-solid fa-pencil text-white"></i>
              </div>
            </div>
            <input type="file" id="avatar-input" name="photo" accept="image/jpeg,image/png,image/webp" style="display: none;">
          </div>
        </form>

        <form id="updateProfile-form">
          <div class="d-flex align-items-center edit-input-group">
            <input type="text" class="form-control form-control-plaintext edit-label flex-grow-1 update-usn"
              placeholder="Enter Callname..." value="Callname">
            <i class="fa-solid fa-pencil edit-icon"></i>
          </div>

          <div class="d-flex align-items-start edit-input-group" style="height: 100px; padding: 15px;">
            <textarea class="form-control form-control-plaintext edit-label flex-grow-1 update-bio"
              placeholder="Tell us about yourself..." style="resize: none; height: 100%;">Bio</textarea>
            <i class="fa-solid fa-pencil edit-icon"></i>
          </div>

          <div class="d-flex align-items-center edit-input-group">
            <input type="text" class="form-control form-control-plaintext edit-label flex-grow-1 update-edu"
              placeholder="Enter Education details..." value="Education">
            <i class="fa-solid fa-pencil edit-icon"></i>
          </div>

          <div class="text-center mt-5">
            <button type="submit" class="save-btn">Save</button>
          </div>
        </form>
      </div>
    </main>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="/js/updateProfile.js"></script>
</body>

</html>
