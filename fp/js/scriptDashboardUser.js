function getCookie(name) {
  const cookies = document.cookie.split("; ");
  const cookieMap = {};
  cookies.forEach((cookie) => {
    const [name, value] = cookie.split("=");
    cookieMap[name] = value;
  });

  return cookieMap[name];
}

function renderLatestApplication(applications) {
  if (!applications.length) return;

  const latest = applications[0];
  const card = document.getElementById("latest-application");

  card.querySelector(".card-title").textContent = latest.job_title;
  card.querySelector(".card-desc").textContent =
    `Status: ${latest.status} • Applied on ${new Date(latest.applied_at).toLocaleDateString()}`;
}

function renderApplicationList(applications) {
  const container = document.getElementById("application-list");
  container.innerHTML = "";

  if (applications.length === 0) {
    container.innerHTML = `<p class="text-muted mt-3">No applications yet.</p>`;
    return;
  }

  applications.forEach((app) => {
    const row = document.createElement("div");
    row.className = "list-row";

    row.innerHTML = `
      <div class="col-5 d-flex align-items-center">
        <div class="mini-avatar"></div>
        <span class="fw-bold fs-6">${app.job_title}</span>
      </div>

      <div class="col-5 d-flex align-items-center">
        <span class="fw-bold fs-6">${app.status}</span>
      </div>

      <div class="col-2 text-end">
        <a href="/jobs/${app.job_id}" class="see-more-link">See More</a>
      </div>
    `;

    container.appendChild(row);
  });
}

document.addEventListener("DOMContentLoaded", function () {
  const progressBars = document.querySelectorAll(".progress-bar");
  const logoutBtn = document.querySelector("#btn-logout");

  const row = document.getElementById("job-row");

  let page = 1;
  let isLoading = false;
  let hasMore = true;

  async function fetchApplications() {
    const res = await fetch("/api/applications", {
      credentials: "include",
      headers: {
        "X-Authorization": "Bearer " + getCookie("api_token"),
      },
    });

    const json = await res.json();
    return json.data?.data || [];
  }

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

  setTimeout(() => {
    progressBars.forEach((bar) => {
      const targetWidth = bar.getAttribute("data-width");
      bar.style.width = targetWidth;
    });
  }, 300);

  (async function initDashboard() {
    try {
      const applications = await fetchApplications();
      renderApplicationList(applications);
      renderLatestApplication(applications);
    } catch (err) {
      console.error("Failed to load applications", err);
    }
  })();
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
