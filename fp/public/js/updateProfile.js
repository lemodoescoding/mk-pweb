function getCookie(name) {
  const cookies = document.cookie.split("; ");
  const cookieMap = {};
  cookies.forEach((cookie) => {
    const [name, value] = cookie.split("=");
    cookieMap[name] = value;
  });

  return cookieMap[name];
}
// --- DOM Elements ---
const callnameInput = document.querySelector('input[value="Callname"]');
const usernameInput = document.querySelector('input[value="Username"]');
const bioTextarea = document.querySelector(
  'textarea[placeholder="Tell us about yourself..."]',
);
const educationInput = document.querySelector('input[value="Education"]');
const profileForm = document.querySelector("form");
const sidebarLogoutBtn = document.querySelector(
  ".sidebar .mt-auto .sidebar-box",
); // Logout button in the sidebar

// --- API Endpoints ---
const API_PROFILE_FETCH = "/api/user/profile";
const API_PROFILE_UPDATE = "/api/profile/profile";
const API_LOGOUT = "/api/auth/logout"; // POST for logout
const API_PROFILE_PLACEHOLDER = "/api/profile/placeholder";

// --- Function to Load User Data ---
async function loadUserData() {
  const token = getCookie("api_token");
  if (!token) {
    return;
  }

  try {
    const response = await fetch(API_PROFILE_FETCH, {
      method: "GET",
      headers: {
        Authorization: "Bearer " + token,
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

// --- Function to Handle Form Submission (Save) ---
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
  if (!payload.callname || !payload.username) {
    alert("Callname and Username are required.");
    return;
  }

  try {
    const response = await fetch(API_PROFILE_UPDATE, {
      method: "PUT", // or 'POST', depending on your backend REST convention
      headers: {
        Authorization: "Bearer " + token,
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        bio: payload.bio,
        last_education: payload.education,
      }),
    });

    const response2 = await fetch(API_PROFILE_PLACEHOLDER, {
      method: "PUT",
      headers: {
        Authorization: "Bearer " + token,
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
      Authorization: "Bearer " + api_token,
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
