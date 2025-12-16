function getCookie(name) {
  const cookies = document.cookie.split("; ");
  const cookieMap = {};
  cookies.forEach((cookie) => {
    const [name, value] = cookie.split("=");
    cookieMap[name] = value;
  });

  return cookieMap[name];
}

async function deleteUser(userId) {
  if (
    !confirm(
      `Are you sure you want to delete User ID ${userId} and ALL associated data? This action cannot be undone.`,
    )
  ) {
    return;
  }

  const rowElement = document.getElementById(`user-row-${userId}`);
  rowElement.style.opacity = "0.5";

  try {
    const token = getCookie("api_token");

    const res = await fetch(`/api/user/delete/${userId}`, {
      method: "DELETE",
      headers: {
        "X-Authorization": "Bearer " + token,
      },
    });

    const json = await res.json();

    if (res.ok && json.status) {
      console.log(`User ${userId} deleted successfully.`);

      rowElement.remove();
      updateStats();
      alert(`User ${userId} deleted.`);
    } else {
      throw new Error(json.message || "Deletion failed on server.");
    }
  } catch (err) {
    console.error("Error deleting user:", err);
    alert("Failed to delete user. Check console for details.");
    rowElement.style.opacity = "1"; // Restore opacity on failure
  }
}

async function promoteUser(userId) {
  if (!confirm(`Are you sure you want to promote User ID ${userId}?`)) {
    return;
  }

  const rowElement = document.getElementById(`user-row-${userId}`);
  rowElement.style.opacity = "0.5";

  try {
    const token = getCookie("api_token");

    const res = await fetch(`/api/user/promote/${userId}`, {
      method: "PUT",
      headers: {
        "X-Authorization": "Bearer " + token,
      },
    });

    const json = await res.json();

    if (res.ok && json.status) {
      console.log(`User ${userId} promoted successfully.`);

      rowElement.innerHTML = `
                <div class="col-5 d-flex align-items-center">
                    <div class="mini-avatar"></div>
                    <span class="fw-bold fs-6">${user.username}</span>
                </div>
                <div class="col-5 d-flex align-items-center">
                    <span class="fw-bold fs-6">${user.role}</span>
                </div>
                <div class="col-2 text-end action-links">
                    <span class="action-link delete-btn" data-id="${user.id}" style="cursor: pointer; color: #dc3545;"><i class="fa-solid fa-trash"></i> Delete</span>
                </div>`;
    } else {
      throw new Error(json.message || "Promotion failed on server.");
    }
  } catch (err) {
    console.error("Error promoting user:", err);
    alert("Failed to promote user. Check console for details.");
    rowElement.style.opacity = "1"; // Restore opacity on failure
  }
}

const updateStats = async () => {
  const statsContainer = document.getElementById("admin-stats");

  try {
    const token = getCookie("api_token");

    const res = await fetch("/api/admin/stats", {
      headers: {
        "X-Authorization": "Bearer " + token,
      },
    });

    const resJobs = await fetch("/api/jobs/page/1", {
      headers: {
        "X-Authorization": "Bearer " + token,
      },
    });

    const resApplies = await fetch("/api/applications/count", {
      headers: {
        "X-Authorization": "Bearer " + token,
      },
    });

    const json = await res.json();
    const job_json = await resJobs.json();
    const apl_json = await resApplies.json();

    if (!json.status) throw new Error();
    if (!job_json.status) throw new Error();

    const { total_users, total_admins } = json.data;
    const { jobs, countAll, currStart } = job_json.data;
    const { count } = apl_json.data;

    statsContainer.innerHTML = `
      <div class="stat-item">
        <strong>${total_users}</strong>
        <span>Total Users</span>
      </div>
      <div class="stat-item">
        <strong>${total_admins}</strong>
        <span>Admins</span>
      </div>
      <div class="stat-item">
        <strong>${countAll}</strong>
        <span>Jobs</span>
      </div>
      <div class="stat-item">
        <strong>${count}</strong>
        <span>Applications</span>
      </div>
    `;
  } catch (err) {
    console.log(err);
    statsContainer.innerHTML = `<div class="text-danger">Failed to load stats</div>`;
  }
};
document.addEventListener("DOMContentLoaded", updateStats);

document.addEventListener("DOMContentLoaded", function () {
  const userList = document.getElementById("user-list");

  const renderUsers = (users) => {
    userList.innerHTML = ""; // clear existing

    users.forEach((user) => {
      const row = document.createElement("div");
      // 1. Add a unique ID to the row for easy DOM manipulation after deletion
      row.id = `user-row-${user.id}`;
      row.className = "list-row";

      row.innerHTML =
        `
                <div class="col-5 d-flex align-items-center">
                    <div class="mini-avatar"></div>
                    <span class="fw-bold fs-6">${user.username}</span>
                </div>
                <div class="col-5 d-flex align-items-center">
                    <span class="fw-bold fs-6">${user.role}</span>
                </div>
                <div class="col-2 text-end action-links">
                ` +
        (user.role != "admin"
          ? `
                    <span class="action-link promote-btn" data-id="${user.id}" style="cursor: pointer;"><i class="fa-solid fa-arrow-up"></i> Promote</span>
                  <br>
                    <span class="action-link delete-btn" data-id="${user.id}" style="cursor: pointer; color: #dc3545;"><i class="fa-solid fa-trash"></i> Delete</span>
                `
          : `
                    <span class="action-link delete-btn" data-id="${user.id}" style="cursor: pointer; color: #dc3545;"><i class="fa-solid fa-trash"></i> Delete</span>
                </div>
            `);

      userList.appendChild(row);
    });

    attachDeleteListeners();
  };

  const attachDeleteListeners = () => {
    const deleteButtons = document.querySelectorAll(".delete-btn");
    deleteButtons.forEach((btn) => {
      btn.addEventListener("click", (e) => {
        e.preventDefault(); // Prevent any default action just in case
        const userId = parseInt(btn.getAttribute("data-id"));
        if (userId) {
          deleteUser(userId);
        }
      });
    });

    const promoteButtons = document.querySelectorAll(".promote-btn");
    promoteButtons.forEach((btn) => {
      btn.addEventListener("click", (e) => {
        e.preventDefault(); // Prevent any default action just in case
        const userId = parseInt(btn.getAttribute("data-id"));
        if (userId) {
          promoteUser(userId);
        }
      });
    });
  };

  const fetchUsers = async () => {
    try {
      const res = await fetch("/api/admin/users", {
        headers: {
          "X-Authorization": "Bearer " + getCookie("api_token"),
        },
      });

      const json = await res.json();

      if (!json.status || !Array.isArray(json.data.users)) {
        throw new Error("Invalid response");
      }

      renderUsers(json.data.users);
    } catch (err) {
      console.error("Failed to load users:", err);
      userList.innerHTML = "<p class='text-muted'>Failed to load users</p>";
    }
  };

  fetchUsers();

  const row = document.getElementById("job-row");

  let page = 1;
  let isLoading = false;
  let hasMore = true;

  async function fetchJobs(page) {
    const res = await fetch(`/api/jobs/page/${page}`);
    const json = await res.json();

    if (!json.status || !json.data?.jobs?.length) {
      hasMore = false;
      return [];
    }

    return json.data.jobs;
  }

  function renderJobs(jobs) {
    const fragment = document.createDocumentFragment();

    jobs.forEach((job) => {
      const col = document.createElement("div");
      col.className = "col-md-4";

      col.innerHTML = `
        <div class="card-custom">
          <div class="img-placeholder"></div>
          <div class="card-body-custom">
            <div class="card-title">${job.title}</div>
            <div class="card-desc">
              ${job.company}<br>
              ${job.city}, ${job.state}
            </div>
          </div>
        </div>
      `;

      fragment.appendChild(col);
    });

    row.appendChild(fragment);
  }

  async function loadMore() {
    if (isLoading || !hasMore) return;

    isLoading = true;
    const jobs = await fetchJobs(page);
    renderJobs(jobs);
    page++;
    isLoading = false;
  }

  row.addEventListener("scroll", () => {
    const threshold = 150;

    if (row.scrollWidth - row.scrollLeft <= row.clientWidth + threshold) {
      loadMore();
    }
  });

  loadMore();

  setTimeout(() => {
    progressBars.forEach((bar) => {
      const targetWidth = bar.getAttribute("data-width");
      bar.style.width = targetWidth;
    });
  }, 300);

  const cards = document.querySelectorAll(".card-custom");
  cards.forEach((card) => {
    card.addEventListener("click", () => {
      card.style.transform = "scale(0.98)";
      setTimeout(() => {
        card.style.transform = "";
      }, 150);
    });
  });

  logoutBtn.addEventListener("click", () => {
    const api_token = getCookie("api_token");

    fetch("/api/auth/logout", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-Authorization": "Bearer " + api_token,
      },
    })
      .then((res) => {
        if (res.ok) {
          console.log(res.status);
          return res.json();
        }
      })
      .then((res) => {
        console.log(res);
        if (res.status) {
          window.location.href = "/";
        }
      });
  });
});

const currentAvatar = document.getElementById("current-avatar");
const placeholderIcon = document.getElementById("photo-placeholder-icon");

function renderUserProfile(data) {
  if (data.profile && data.profile.photo) {
    currentAvatar.src = data.profile.photo;
    currentAvatar.style.display = "block";
    placeholderIcon.style.display = "none";
  } else {
    currentAvatar.style.display = "none";
    placeholderIcon.style.display = "block";
  }

  console.log("Profile data loaded:", data);
}

document.addEventListener("DOMContentLoaded", async () => {
  try {
    const response = await fetch("/api/user/profile", {
      method: "GET",
      headers: {
        "X-Authorization": `Bearer ${getCookie("api_token")}`,
      },
    });
    const result = await response.json();

    if (response.ok) {
      renderUserProfile(result.data);
    } else {
      console.error("Failed to load profile data:", result.message);
      // Optionally redirect to login or show an error state
    }
  } catch (error) {
    console.error("Network error during profile load:", error);
  }
});
