<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register Page</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <link href="../css/styleLoginRegister.css" rel="stylesheet">
</head>

<body class="d-flex flex-column justify-content-center align-items-center">

  <div class="login-card mx-3 mt-5 mb-0">
    <h2 class="text-center mb-4">Register</h2>

    <form id="registerForm">
      <div class="mb-3">
        <input type="text" class="form-control" id="username" placeholder="Username" required>
      </div>

      <div class="mb-3">
        <input type="email" class="form-control" id="email" placeholder="Email" required>
      </div>

      <div class="mb-3">
        <input type="password" class="form-control" id="password" placeholder="Password" required>
      </div>

      <div class="mb-3">
        <input type="password" class="form-control" id="password_confirm" placeholder="Confirm Password" required>
      </div>

      <div class="text-end mb-4">
        <a href="/login" class="text-register">Login here</a>
      </div>

      <div class="d-grid mb-4">
        <button type="submit" class="btn btn-submit text-white" href="../index.html">Submit</button>
      </div>
    </form>

    <div class="separator mb-4">
      <span class="separator-text">OR</span>
    </div>

    <div class="d-grid">
      <button class="btn btn-google d-flex align-items-center justify-content-center">
        <img src="../img/component/g.png" class="google-icon">
        Register with Google
      </button>
    </div>
  </div>

  <div class="footer-bar"></div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

  <script>
    document.getElementById('googleLoginBtn').addEventListener('click', () => {
      // Just redirect — backend handles everything
      window.location.href = '/api/auth/google';
    });
  </script>
  <script src="/js/register.js"></script>
</body>

</html>
