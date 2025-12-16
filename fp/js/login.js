function escapeHTML(str) {
  return str
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

document.addEventListener("DOMContentLoaded", function () {
  const usernameEl = document.querySelector("#username");
  const passwordEl = document.querySelector("#password");
  const submit = document.querySelector("#submit-login");

  submit.addEventListener("click", (e) => {
    e.preventDefault();

    fetch("/api/auth/login", {
      method: "POST",
      body: JSON.stringify({
        username: escapeHTML(usernameEl.value),
        password: escapeHTML(passwordEl.value),
      }),
    }).then((res) => {
      if (res.ok) {
        document.location.href = "/";
      }
    });
  });
});
