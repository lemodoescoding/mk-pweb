function getCookie(name) {
  const cookies = document.cookie.split("; ");
  const cookieMap = {};
  cookies.forEach((cookie) => {
    const [name, value] = cookie.split("=");
    cookieMap[name] = value;
  });

  return cookieMap[name];
}
const callnameInput = document.querySelector('input.update-usn');
const bioTextarea = document.querySelector(
  'textarea.update-bio',
);
const educationInput = document.querySelector('input.update-edu');
const profileForm = document.querySelector("#updateProfile-form");
const sidebarLogoutBtn = document.querySelector(
  ".sidebar .mt-auto .sidebar-box",
);

const avatarForm = document.getElementById("avatar-form");
const avatarInput = document.getElementById("avatar-input");
const profileImagePlaceholder = document.getElementById(
  "profile-image-placeholder",
);
const currentAvatar = document.getElementById("current-avatar");
const placeholderIcon = document.getElementById("placeholder-icon");
const avatarEditOverlay = document.getElementById("avatar-edit-overlay");

const API_PROFILE_FETCH = "/api/user/profile";
const API_PROFILE_UPDATE = "/api/profile/profile";
const API_LOGOUT = "/api/auth/logout"; // POST for logout
const API_PROFILE_PLACEHOLDER = "/api/profile/placeholder";

async function loadUserData() {
  const token = getCookie("api_token");
  if (!token) {
    return;
  }

  try {
    const response = await fetch(API_PROFILE_FETCH, {
      method: "GET",
      headers: {
        "X-Authorization": "Bearer " + token,
        "Content-Type": "application/json",
      },
    });

    const { data } = await response.json();

    if (response.ok) {
      callnameInput.value = data.placeholder || "";
      bioTextarea.value = data.profile.bio || "";
      educationInput.value = data.profile.education || "";
    } else {
      console.error("Failed to load user profile:", data.message);
    }
  } catch (error) {
    console.error("Network error during profile fetch:", error);
  }
}

async function handleFormSubmit(e) {
  e.preventDefault();

  const token = getCookie("api_token");
  if (!token) {
    return;
  }

  // 1. Build Payload
  const payload = {
    callname: callnameInput.value.trim(),
    bio: bioTextarea.value.trim(),
    education: educationInput.value.trim(),
    // Note: Image upload requires a different approach (FormData/multi-part)
    // For simplicity, we only handle text fields here.
  };

  // Check if any critical field is empty
  if (!payload.callname) {
    alert("Callname are required.");
    return;
  }

  try {
    const response = await fetch(API_PROFILE_UPDATE, {
      method: "PUT", // or 'POST', depending on your backend REST convention
      headers: {
        "X-Authorization": "Bearer " + token,
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        bio: payload.bio,
        last_education: payload.education ?? '',
      }),
    });

    const response2 = await fetch(API_PROFILE_PLACEHOLDER, {
      method: "PUT",
      headers: {
        "X-Authorization": "Bearer " + token,
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        placeholder: payload.callname,
      }),
    });

    const data = await response.json();
    const data2 = await response2.json();

    if (response.ok && data.status && response2.ok && data2.status) {
      alert("Profile updated successfully!");
    } else {
      alert(data.message || "Failed to update profile.");
      console.error("Update failed:", data);
    }
  } catch (error) {
    alert("A network error occurred during profile update.");
    console.error("Network error during profile update:", error);
  }
}

// --- Function to Handle Logout (for sidebar button) ---
function handleLogout() {
  const api_token = getCookie("api_token");
  if (!api_token) {
    window.location.href = "/";
    return;
  }

  fetch(API_LOGOUT, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "X-Authorization": "Bearer " + api_token,
    },
  })
    .then((res) => res.json())
    .then((res) => {
      if (res.status) {
        // Assuming successful logout clears the cookie on the server
        window.location.href = "/";
      } else {
        alert("Logout failed: " + res.message);
      }
    })
    .catch((err) => {
      console.error("Logout network error:", err);
      window.location.href = "/"; // Redirect anyway for security
    });
}

// --- Event Listeners and Initialization ---
document.addEventListener("DOMContentLoaded", () => {
  loadUserData();
  if (profileForm) {
    profileForm.addEventListener("submit", handleFormSubmit);
  }

  if (sidebarLogoutBtn) {
    sidebarLogoutBtn.addEventListener("click", handleLogout);
  }
});

// UPLOAD AVATAR

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

profileImagePlaceholder.addEventListener("click", () => {
  avatarInput.click();
});

profileImagePlaceholder.addEventListener("mouseenter", () => {
  avatarEditOverlay.style.opacity = 1;
});
profileImagePlaceholder.addEventListener("mouseleave", () => {
  avatarEditOverlay.style.opacity = 0;
});

avatarInput.addEventListener("change", async () => {
  if (avatarInput.files.length > 0) {
    const file = avatarInput.files[0];

    const allowedTypes = ["image/jpeg", "image/png", "image/webp"];
    if (!allowedTypes.includes(file.type)) {
      alert("Invalid file type. Only JPG, PNG, and WebP are allowed.");
      avatarInput.value = "";
      return;
    }

    const formData = new FormData(avatarForm);

    placeholderIcon.classList.remove("fa-user-circle");
    placeholderIcon.classList.add("fa-spinner", "fa-spin");

    try {
      const response = await fetch("/api/profile/avatar", {
        method: "POST",
        body: formData,
        headers: {
          "X-Authorization": `Bearer ${getCookie("api_token")}`,
        },
      });

      const result = await response.json();

      if (response.ok) {
        alert("Avatar updated successfully!");
        currentAvatar.src = result.data.avatar; // Use the path returned from PHP
        currentAvatar.style.display = "block";
        placeholderIcon.style.display = "none";
      } else {
        alert(`Error: ${result.message || "Failed to upload photo."}`);
      }
    } catch (error) {
      console.error("Upload failed:", error);
      alert("A network error occurred during upload.");
    } finally {
      // Reset loading state
      placeholderIcon.classList.remove("fa-spinner", "fa-spin");
      placeholderIcon.classList.add("fa-user-circle");
      avatarInput.value = ""; // Clear input for next upload
    }
  }
});
