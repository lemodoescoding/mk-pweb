<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ConnectIn</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="/css/styleIndex.css" rel="stylesheet">
</head>

<body>
  <div class="main-layout d-flex">
    <aside id="sidebar" class="sidebar d-flex flex-column">
      <div class="p-3 user-profile">
        <div class="d-flex align-items-center mb-4">
          <a href="/updateProfile">
            <svg class="profile-icon me-3" fill="currentColor" viewBox="0 0 16 16">
              <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0" />
              <path d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.758 1.226 5.468 2.37A7 7 0 0 0 8 1" />
            </svg>
          </a>

          <div>
            <a>
              <h5 class="m-0"><?= $username ?></h5>
            </a>
            <p class="m-0 text-secondary small">@<?= (empty($placeholder) ? $username : $placeholder) ?></p>
            <!-- <a href="./html/404.html">404</a> -->
            <!-- <a href="./html/403.html">403</a> -->
          </div>
        </div>
      </div>

      <nav class="nav-links flex-grow-1">
        <a href="/" class="nav-link">Home</a>
        <!-- <a href="/categories" class="nav-link">Categories</a> -->
        <!-- <a href="./html/indexHelp.html" class="nav-link">Help</a> -->
        <?php if ($role === 'admin' && isset($role)): ?>
          <a href="/admin" class="nav-link">Admin Dashboard</a>
        <?php else: ?>

          <a href="/dashboard" class="nav-link">User Dashboard</a>
        <?php endif; ?>
        <a href="/updateProfile" class="nav-link">User Settings</a>
      </nav>

      <div class="bottom-bar p-3 mt-auto">
        <!-- <a href="./html/testpage.html" class="d-block text-white text-decoration-none py-1">Account Settings</a> -->
        <a class="d-block text-white text-decoration-none py-1" id="btn-logout" style="cursor: pointer;">Log Out</a>
      </div>
    </aside>

    <main class="content flex-grow-1">

      <div class="search-bar">
        <input type="text" class="form-control search-input" placeholder="Search" id="search-bar">
      </div>

      <div id="post-feed" class="post-feed p-3">
      </div>

      <?php if ($role == 'admin' && isset($role)): ?>
        <button id="post-button" class="btn post-btn position-fixed">
          Post +
        </button>
      <?php endif; ?>

    </main>
  </div>

  <div class="modal fade" id="applyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Apply to Job</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Are you sure you want to apply for this position?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
          <button type="button" class="btn btn-success">Yes</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="jobDetailTitle"></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p id="jobDetailCompany"></p>
          <p id="jobDetailLocation"></p>
          <p id="jobDetailSalary"></p>
          <p id="jobDetailCategory"></p>
          <hr>
          <p id="jobDetailDescription"></p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button class="btn btn-success" id="btnApplyJob">Save</button>
        </div>
      </div>
    </div>
  </div>
  </div>

  <div class="modal fade" id="postJobModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Post a Job</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <form id="post-job-form">
            <div class="mb-3">
              <label class="form-label">Job Title</label>
              <input type="text" class="form-control" name="title" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Category</label>
              <input type="text" class="form-control" name="category" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Description</label>
              <textarea class="form-control" rows="4" name="description" required></textarea>
            </div>

            <div class="mb-3">
              <label class="form-label">Recruiter Information</label>
              <input class="form-control" placeholder="Employer Name" name="employer_name" required></input>
              <input class="form-control" placeholder="Logo Link" name="employer_logo" required></input>
              <input class="form-control" placeholder="Website" name="employer_website" required></input>
            </div>

            <div class="mb-3">
              <label class="form-label">Apply Link</label>
              <input type="url" class="form-control" name="apply_link" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Salary (Monthly)</label>
              <div class="d-flex gap-2">
                <input type="number" class="form-control" name="min_salary" placeholder="Min" required>
                <input type="number" class="form-control" name="max_salary" placeholder="Max" required>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label">Location</label>
              <div class="d-flex gap-2">
                <input type="text" class="form-control" name="city" placeholder="City" required>
                <input type="text" class="form-control" name="state" placeholder="State" required>
                <input type="text" class="form-control" name="country" placeholder="Country" required>
              </div>
            </div>

            <div class="form-check mb-3">
              <input class="form-check-input" type="checkbox" name="is_remote">
              <label class="form-check-label">Remote Job</label>
            </div>

            <button type="submit" class="btn btn-success w-100">
              Publish Job
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="deleteModalTitle">Confirm Deletion</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="deleteModalBody">
          Are you sure you want to delete this job posting? This action cannot be undone.
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-danger" id="btnConfirmDelete" data-job-id="">Delete Forever</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="editJobModal" tabindex="-1" aria-labelledby="editJobModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content" style="min-width: 700px">
        <div class="modal-header">
          <h5 class="modal-title" id="editJobModalLabel">Edit Job Posting</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="edit-job-form">
          <div class="modal-body">
            Main Detail
            <textarea name="description" placeholder="Description" class="form-control mb-2" required></textarea>
            <input type="text" name="apply_link" placeholder="Apply Link" class="form-control mb-2" required>
            <hr>
            Employer Data
            <input class="form-control" placeholder="Employer Name" name="employer_name" required>
            <input class="form-control" placeholder="Logo Link" name="employer_logo">
            <input class="form-control" placeholder="Website" name="employer_website">
            <hr>
            Location
            <input type="text" name="city" placeholder="City" class="form-control mb-2" required>
            <input type="text" name="state" placeholder="State" class="form-control mb-2" required>
            <input type="text" name="country" placeholder="Country" class="form-control mb-2" required>
            Is Remote?: <input class="form-check-input" type="checkbox" name="is_remote">
            <label class="form-check-label">Remote Job</label>
            <hr>
            Salary:
            <input type="number" class="form-control" name="min_salary" placeholder="Min" required>
            <input type="number" class="form-control" name="max_salary" placeholder="Max" required>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Save Changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="/js/scriptIndex.js"></script>
</body>

</html>
