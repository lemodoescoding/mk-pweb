function truncateString(str, maxLength) {
  return str.length > maxLength ? str.slice(0, maxLength) + "..." : str;
}

function getCookie(name) {
  const cookies = document.cookie.split("; ");
  const cookieMap = {};
  cookies.forEach((cookie) => {
    const [name, value] = cookie.split("=");
    cookieMap[name] = value;
  });

  return cookieMap[name];
}

function debounce(fn, delay = 500) {
  let timer;

  return function (...args) {
    clearTimeout(timer);

    timer = setTimeout(() => {
      fn.apply(this, args);
    }, delay);
  };
}

function clearStuckBackdrop() {
  // Attempt to remove the body class and backdrop if they somehow persist
  document.body.classList.remove("modal-open");

  // Remove the stuck backdrop element itself
  const backdrop = document.querySelector(".modal-backdrop");
  if (backdrop) {
    backdrop.remove();
  }
}

document.addEventListener("DOMContentLoaded", () => {
  const postFeed = document.getElementById("post-feed");
  const contentArea = document.querySelector(".content");
  const searchBar = document.querySelector("#search-bar");
  const logoutBtn = document.querySelector("#btn-logout");

  const postBtn = document.getElementById("post-button");

    const postJobModal = new bootstrap.Modal(
      document.getElementById("postJobModal"),
    );

    postBtn.addEventListener("click", () => {
      postJobModal.show();
    });

    document
      .getElementById("post-job-form")
      .addEventListener("submit", async (e) => {
        e.preventDefault();

        const form = e.target;

        const payload = {
          title: form.title.value.trim(),
          description: form.description.value.trim(),
          category_name: form.category.value.trim(),
          employer_name: form.employer_name.value.trim(),
          employer_logo: form.employer_logo.value.trim(),
          employer_website: form.employer_website.value.trim(),
          apply_link: form.apply_link.value.trim(),
          min_salary: form.min_salary.value || null,
          max_salary: form.max_salary.value || null,
          city: form.city.value,
          state: form.state.value,
          country: form.state.value,
          is_remote: form.is_remote.checked ? 1 : 0,
        };

        try {
          const token = getCookie("api_token");

          console.log(token);
          const res = await fetch("/api/jobs/create", {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
              Authorization: "Bearer " + token,
            },
            body: JSON.stringify(payload),
          });

          const json = await res.json();

          if (!res.ok) {
            alert(json.message || "Failed to post job");
            return;
          }

          alert("Job posted successfully!");
          form.reset();
          postJobModal.hide();

          clearStuckBackdrop();
        } catch (err) {
          console.error(err); // alert("Network error");
        }
      });
  


  let page = 1;
  let searchPage = 1;
  let isLoading = false;
  let isSearching = false;

  const searchJobs = async (query, page) => {
    try {
      const res = await fetch(
        `/api/jobs/search/${encodeURIComponent(query)}/${page}`,
      );
      const json = await res.json();

      if (!json.status || !json.data?.jobs?.length) return [];

      return json.data.jobs.map((job) => ({
        id: `job-${job.id}`,
        title: job.title,
        content: `
          Company: ${job.company}<br>
          Salary: ${
          job.min_salary
            ? new Intl.NumberFormat("en-US", {
                style: "currency",
                currency: "USD",
              }).format(job.min_salary)
            : "N/A"
        } -
          ${
          job.max_salary
            ? new Intl.NumberFormat("en-US", {
                style: "currency",
                currency: "USD",
              }).format(job.max_salary)
            : "N/A"
        }<br>
          Location: ${job.city}, ${job.state}, ${job.country}<br><br>
          Deskripsi: ${truncateString(job.description, 250)}
        `,
        className: "",
        id_post: job.id,
      }));
    } catch (err) {
      console.error("Search error:", err);
      return [];
    }
  };

  const fetchPosts = async (currentPage) => {
    try {
      return await fetch(`/api/jobs/page/${currentPage}`)
        .then((res) => res.json())
        .then((res) => {
          const { data, status } = res;

          const { jobs, currStart, countAll } = data;

          if (!status || !Array.isArray(jobs) || jobs.length === 0) {
            hasMore = false;
            return [];
          }

          const newPosts = [];
          for (let i = 0; i < jobs.length; i++) {
            const postNumber = (currentPage - 1) * jobs.length + i + 1;

            if (jobs[i].min_salary == 0) {
              jobs[i].min_salary = "N/A";
            } else {
              jobs[i].min_salary = new Intl.NumberFormat({
                style: "currency",
                currency: "US",
              }).format(jobs[i].min_salary);
            }

            if (jobs[i].max_salary == 0) {
              jobs[i].max_salary = "N/A";
            } else {
              jobs[i].max_salary = new Intl.NumberFormat({
                style: "currency",
                currency: "US",
              }).format(jobs[i].max_salary);
            }

            newPosts.push({
              id: `post-${postNumber}`,
              title: `${jobs[i].title}`,
              content:
                `Company: ${jobs[i].company}<br>Salary: $${jobs[i].min_salary} - $${jobs[i].max_salary}<br>Salary Period: ${jobs[i].salary_period}<br>` +
                `
<br>Location: ${jobs[i].city + ", " + jobs[i].state + ", " + jobs[i].country}<br><br>Deskripsi: ${truncateString(jobs[i].description, 250)}`,
              className: "",
              id_post: jobs[i].id,
            });
          }

          console.log(newPosts);
          return newPosts;
        });
    } catch (err) {
      console.error("Error fetching posts:", err);
      return [];
    }
  };

  const renderPosts = (posts) => {
    const fragment = document.createDocumentFragment();

    posts.forEach((post) => {
      const postElement = document.createElement("div");
      postElement.className = `post-card p-3 job-post-card ${post.className}`; // Added job-post-card class
      postElement.setAttribute("data-id", post.id);
      postElement.innerHTML =
        `<div><h4>${post.title}</h4>
                    <p>${post.content}</p>
                </div>
                <div class="d-flex gap-2 justify-content-end mt-auto">
        ` +
        (getCookie("role") == "admin"
          ? `<button class="btn btn-warning btn-sm btn-edit-detail" data-bs-toggle="modal" data-bs-target="#editModal" data-title="${post.title}" data-content="${post.content}" data-job-id="${post.id_post}">Edit</button><button class="btn btn-danger btn-sm btn-delete-detail" data-bs-toggle="modal" data-bs-target="#deleteModal" data-title="${post.title}" data-content="${post.content}" data-job-id="${post.id_post}">Delete</button>
`
          : ``) +
        `<button class="btn btn-primary btn-sm btn-view-detail" data-bs-toggle="modal" data-bs-target="#detailModal" data-title="${post.title}" data-content="${post.content}" data-job-id="${post.id_post}">View Detail</button>
        <button class="btn btn-success btn-sm btn-apply-trigger" data-bs-toggle="modal" data-bs-target="#applyModal" data-job-id="${post.id_post}">Save</button>
</div>`;

      fragment.appendChild(postElement);
    });

    postFeed.appendChild(fragment);
  };

  const loadMorePosts = () => {
    if (isLoading) return;

    isLoading = true;

    setTimeout(async () => {
      const posts = await fetchPosts(page);
      renderPosts(posts);
      page++;
      isLoading = false;
    }, 0);
  };

  loadMorePosts();

  contentArea.addEventListener("scroll", async () => {
    if (isLoading) return;

    const threshold = 100;
    if (
      contentArea.scrollHeight - contentArea.scrollTop <=
      contentArea.clientHeight + threshold
    ) {
      isLoading = true;

      if (isSearching) {
        searchPage++;
        const posts = await searchJobs(searchBar.value, searchPage);
        renderPosts(posts);
      } else {
        const posts = await fetchPosts(page);
        renderPosts(posts);
        page++;
      }

      isLoading = false;
    }
  });

  const debouncedSearch = debounce(async (e) => {
    postFeed.innerHTML = "";

    console.log(e.target.value.trim());

    const query = e.target.value.trim();

    page = 1;
    searchPage = 1;

    if (!query) {
      isSearching = false;
      loadMorePosts();
      return;
    }

    isSearching = true;
    const posts = await searchJobs(query, searchPage);
    renderPosts(posts);
  }, 500);

  searchBar.addEventListener("input", debouncedSearch);

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


  let selectedJobId = null;

  document.addEventListener("click", async (e) => {
    const btn = e.target.closest(".btn-view-detail");
    if (!btn) return;

    selectedJobId = btn.dataset.jobId;

    if (!selectedJobId) {
      console.error("Job ID not found for detail view.");
      return;
    }

    try {
      const res = await fetch(`/api/jobs/show/${selectedJobId}`);
      const json = await res.json();

      if (!res.ok) {
        alert(json.message || "Failed to load job detail");
        return;
      }

      const { job } = json.data;

      console.log(job);

      document.getElementById("jobDetailTitle").innerText = job.title;
      document.getElementById("jobDetailCompany").innerText =
        "Company: " + job.company;
      document.getElementById("jobDetailLocation").innerText =
        "Location: " + job.city + ", " + job.country;
      document.getElementById("jobDetailSalary").innerText =
        `Salary: ${job.min_salary ? "$" + job.min_salary : "N/A"} - ${job.max_salary ? "$" + job.max_salary : "N/A"} (Period: ${job.salary_period || "N/A"})`;
      document.getElementById("jobDetailCategory").innerText =
        "Category: " + job.category_name;
      document.getElementById("jobDetailDescription").innerText =
        job.description;

      document.getElementById("btnApplyJob").dataset.jobId = selectedJobId;
    } catch (err) {
      console.error(err);
    }
  });

   // APPLY AND VIEW
  document
    .getElementById("btnApplyJob")
    .addEventListener("click", async (e) => {
      const jobIdToApply = e.target.dataset.jobId;

      if (!jobIdToApply) return;

      try {
        const token = getCookie("api_token");
        const res = await fetch(`/api/jobs/apply/${jobIdToApply}`, {
          method: "POST",
          headers: {
            Authorization: "Bearer " + token,
          },
        });

        const json = await res.json();

        if (!res.ok) {
          alert(json.message || "Failed to apply");
          return;
        }

        alert("Application submitted successfully!");
        bootstrap.Modal.getInstance(
          document.getElementById("detailModal"),
        ).hide();
        clearStuckBackdrop();
      } catch (err) {
        console.error(err); // alert("Network error");
      }
    });

  document.addEventListener("click", (e) => {
    const btn = e.target.closest(".btn-apply-trigger");
    if (!btn) return;
    const jobIdToApply = btn.dataset.jobId;

    const modalConfirmBtn = document.querySelector("#applyModal .btn-success");
    if (modalConfirmBtn) {
      modalConfirmBtn.dataset.jobId = jobIdToApply;
    }

    const applyModal = new bootstrap.Modal(
      document.getElementById("applyModal"),
    ).show();
  });

  document
    .querySelector("#applyModal .btn-success")
    .addEventListener("click", async (e) => {
      const jobIdToApply = e.target.dataset.jobId;
      console.log(jobIdToApply);
      if (!jobIdToApply) return;
      try {
        const token = getCookie("api_token");
        const res = await fetch(`/api/jobs/apply/${jobIdToApply}`, {
          method: "POST",
          headers: {
            Authorization: "Bearer " + token,
          },
        });

        const json = await res.json();

        if (!res.ok) {
          alert(json.message || "Failed to apply");
          return;
        }

        alert("Application submitted successfully!");
        bootstrap.Modal.getInstance(
          document.getElementById("applyModal"),
        ).hide();
        clearStuckBackdrop();
      } catch (err) {
        console.error(err);
      }
    });

  document
    .querySelector("#applyModal .btn-secondary")
    .addEventListener("click", async (e) => {
      bootstrap.Modal.getInstance(document.getElementById("applyModal")).hide();
      clearStuckBackdrop();
    });
});

// DELETE
    document.addEventListener("click", (e) => {
      const btn = e.target.closest(
        ".btn-delete-detail[data-bs-target='#deleteModal']",
      );
      if (!btn) return;

      const jobIdToDelete = btn.dataset.jobId;
      const jobTitle = btn.dataset.title;
      const jobContent = btn.dataset.content;

      if (!jobIdToDelete) {
        console.error("Job ID not found for deletion.");
        return;
      }

      // Set data attributes on the modal confirmation button
      const modalConfirmBtn = document.getElementById("btnConfirmDelete");
      if (modalConfirmBtn) {
        modalConfirmBtn.dataset.jobId = jobIdToDelete;
      }

      // Populate the modal content with job details (if you have these elements)
      const deleteModalTitle = document.getElementById("deleteModalTitle");
      const deleteModalBody = document.getElementById("deleteModalBody");

      if (deleteModalTitle) {
        deleteModalTitle.innerText = `Delete Job: ${jobTitle}`;
      }
      if (deleteModalBody) {
        deleteModalBody.innerHTML = `Are you sure you want to delete the job posting for **${jobTitle}**? This action cannot be undone.`;
      }
    });

    document
      .querySelector("#deleteModal #btnConfirmDelete")
      .addEventListener("click", async (e) => {
        const jobIdToDelete = parseInt(e.target.dataset.jobId);

      console.log(jobIdToDelete);

        if (!jobIdToDelete) {
          alert("Error: Job ID is missing.");
          return;
        }

        try {
          const token = getCookie("api_token");

          const res = await fetch(`/api/jobs/delete/${jobIdToDelete}`, {
            method: "DELETE", // Use DELETE method for RESTful deletion
            headers: {
              Authorization: "Bearer " + token,
            },
          });

          const json = await res.json();

          const deleteModalInstance = bootstrap.Modal.getInstance(
            document.getElementById("deleteModal"),
          );
          if (deleteModalInstance) deleteModalInstance.hide();
          clearStuckBackdrop();

          if (!res.ok || !json.status) {
            alert(json.message || "Failed to delete job posting.");
            return;
          }

          alert(`Job posting (ID: ${jobIdToDelete}) deleted successfully!`);

          const postFeed = document.getElementById("post-feed");
          postFeed.innerHTML = "";
          page = 1;
          isSearching = false;

          window.location.href = '/';
        } catch (err) {
          console.error("Delete network error:", err);
          alert("A network error occurred while trying to delete the job.");
        }
      });

// UPDATE
document.addEventListener("click", async (e) => {
    const btn = e.target.closest(".btn-edit-detail");
    if (!btn) return;

    const jobIdToEdit = btn.dataset.jobId;
    const editModalEl = document.getElementById("editJobModal");
    const editForm = document.getElementById("edit-job-form");

    if (!jobIdToEdit || !editForm) {
      console.error("Job ID or Edit Form not found.");
      return;
    }

    try {
      // Fetch data for the specific job
      const res = await fetch(`/api/jobs/show/${jobIdToEdit}`);
      const json = await res.json();

      if (!res.ok || !json.data?.job) {
        alert(json.message || "Failed to load job data for editing.");
        return;
      }

      const { job } = json.data;
      
      // Populate the form fields with job data
      editForm.elements.description.value = job.description || '';
      editForm.elements.employer_name.value = job.company || ''; // Assuming 'company' maps to 'employer_name'
      editForm.elements.employer_logo.value = job.employer_logo || '';
      editForm.elements.employer_website.value = job.employer_website || '';
      editForm.elements.apply_link.value = JSON.parse(job.apply_link)[0]["apply_link"] || '';
      editForm.elements.min_salary.value = job.min_salary ? job.min_salary.toString() : ''; 
      editForm.elements.max_salary.value = job.max_salary ? job.max_salary.toString() : '';
      editForm.elements.city.value = job.city || '';
      editForm.elements.state.value = job.state || '';
      editForm.elements.country.value = job.country || '';
      editForm.elements.is_remote.checked = job.is_remote == 1;
      
      editForm.dataset.jobId = Number(jobIdToEdit); 

      console.log(editForm.dataset.jobId);
      
      // Show the modal
    const modalElement = document.getElementById('editJobModal');
    
    // Get the existing instance or create a new one if it doesn't exist yet
    const editModal = bootstrap.Modal.getOrCreateInstance(modalElement);
    
    editModal.show();
      
    } catch (err) {
      console.error("Fetch job for edit error:", err);
      alert("A network error occurred while loading job data. 1");
    }
  });

  document
    .getElementById("edit-job-form")
    .addEventListener("submit", async (e) => {
      e.preventDefault();

      const form = e.target;
      const jobIdToUpdate = parseInt(form.dataset.jobId);

      const editModalEl = document.getElementById("editJobModal");

      if (!jobIdToUpdate) {
        alert("Error: Job ID not set for update.");
        return;
      }

      const payload = {
        description: form.description.value.trim(),
        employer_name: form.employer_name.value.trim(),
        employer_logo: form.employer_logo.value.trim(),
        employer_website: form.employer_website.value.trim(),
        apply_link: form.apply_link.value.trim(),
        min_salary: form.min_salary.value || null,
        max_salary: form.max_salary.value || null,
        city: form.city.value,
        state: form.state.value,
        country: form.country.value,
        is_remote: form.is_remote.checked ? 1 : 0,
      };

      try {
        const token = getCookie("api_token");

        const res1 = await fetch(`/api/jobs/show/${jobIdToUpdate}`);
        const json1 = await res1.json();
  
        if (!res1.ok || !json1.data?.job) {
          alert(json1.message || "Failed to load job data for editing.");
          return;
        }
        const { job } = json1.data;

        let apply_link_temp = payload.apply_link;
        let apply_links = JSON.parse(job.apply_link);
        apply_links[0]["apply_link"] = apply_link_temp;
        
        payload.apply_link = JSON.stringify(apply_links);
        
        console.log(payload);

        const res = await fetch(`/api/jobs/update/${jobIdToUpdate}`, {
          method: "PUT", // Use PUT method for update
          headers: {
            "Content-Type": "application/json",
            Authorization: "Bearer " + token,
          },
          body: JSON.stringify(payload),
        });

        const json = await res.json();

        if (!res.ok) {
          alert(json.message || "Failed to update job");
          return;
        }

        alert("Job updated successfully!");
        
        // Hide the modal
        const editJobModal = bootstrap.Modal.getInstance(editModalEl);
        if (editJobModal) editJobModal.hide();
        clearStuckBackdrop();

        // Refresh the feed
        isSearching = false;

        window.location.href = '/';

      } catch (err) {
        console.error("Update network error:", err);
        alert("A network error occurred during job update.");
      }
    });
