document.addEventListener("DOMContentLoaded", () => {
  const usernameInput = document.getElementById("username");
  const emailInput = document.getElementById("email");
  const passwordInput = document.getElementById("password");
  const passwordConfirmInput = document.getElementById("password_confirm");
  const regiterForm = document.getElementById("registerForm");

  const displayMessage = (message, isSuccess = false) => {
    alert(message);
  };

  registerForm.addEventListener("submit", async (event) => {
    event.preventDefault();

    const username = usernameInput.value.trim();
    const password = passwordInput.value;
    const password_confirm = passwordConfirmInput.value;

    if (username === "" || password === "" || password_confirm === "") {
      displayMessage("Please fill in all required fields.", false);
      return;
    }

    if (password !== password_confirm) {
      displayMessage("Error: Passwords do not match.", false);
      return;
    }

    const payload = {
      username: username,
      email: emailInput.value,
      password: password,
      password_confirm: password_confirm,
    };

    try {
      const response = await fetch("/api/auth/register", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(payload),
      });

      const data = await response.json();

      if (response.ok) {
        displayMessage(
          data.message || "Registration successful! You can now log in.",
          true,
        );

        window.location.href = "/login";
      } else {
        const errorMessage =
          data.message || `Registration failed with status: ${response.status}`;
        displayMessage(errorMessage, false);
      }
    } catch (error) {
      console.error("Network or parsing error during registration:", error);
      displayMessage("A network error occurred. Please try again.", false);
    }
  });

  const googleBtn = document.querySelector(".btn-google");
  if (googleBtn) {
    googleBtn.addEventListener("click", () => {
      window.location.href = "/api/auth/google";
    });
  } else {
    console.error("Google register button not found. Check HTML selector.");
  }
});
