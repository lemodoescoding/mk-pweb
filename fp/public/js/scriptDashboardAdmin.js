function getCookie(name) {
  const cookies = document.cookie.split("; ");
  const cookieMap = {};
  cookies.forEach((cookie) => {
    const [name, value] = cookie.split("=");
    cookieMap[name] = value;
  });

  return cookieMap[name];
}

document.addEventListener("DOMContentLoaded", async () => {
  const statsContainer = document.getElementById("admin-stats");

  try {
    const token = getCookie("api_token");

    const res = await fetch("/api/admin/stats", {
      headers: {
        Authorization: "Bearer " + token,
      },
    });

    const resJobs = await fetch("/api/jobs/page/1", {
      headers: {
        Authorization: "Bearer " + token,
      },
    });

    const resApplies = await fetch("/api/applications/count", {
      headers: {
        Authorization: "Bearer " + token,
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
});

document.addEventListener("DOMContentLoaded", function () {
  const progressBars = document.querySelectorAll(".progress-bar");
  const logoutBtn = document.querySelector("#btn-logout");

  const userList = document.getElementById("user-list");

  const renderUsers = (users) => {
    userList.innerHTML = ""; // clear existing

    users.forEach((user) => {
      const row = document.createElement("div");
      row.className = "list-row";

      row.innerHTML = `
        <div class="col-5 d-flex align-items-center">
          <div class="mini-avatar"></div>
          <span class="fw-bold fs-6">${user.username}</span>
        </div>
        <div class="col-5 d-flex align-items-center">
          <span class="fw-bold fs-6">${user.role}</span>
        </div>
        <div class="col-2 text-end">
          <a href="/api/admin/users/${user.id}" class="see-more-link">See More</a>
        </div>
      `;

      userList.appendChild(row);
    });
  };

  const fetchUsers = async () => {
    try {
      const res = await fetch("/api/admin/users", {
        headers: {
          Authorization: "Bearer " + getCookie("api_token"),
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
        Authorization: "Bearer " + api_token,
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
