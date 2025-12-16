<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Forbidden</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        .footer-error-code {
            font-size: 8rem; 
            font-weight: 900;
            color: rgba(255, 255, 255, 0.3); 
            line-height: 1;
            user-select: none;
        }
    </style>
</head>
<body class="d-flex flex-column vh-100 bg-light">

    <div class="error-content flex-grow-1 d-flex flex-column justify-content-center align-items-center text-center p-5">
        <h1 class="display-5 text-secondary mb-3">Error - 403</h1>
        <p class="lead text-muted">You do not have permission to view this resource. Contact the administrator if you believe this is an error.</p>
        <a href="/" class="btn btn-primary mt-3">Go to Homepage</a>
    </div>

    <div class="footer-bar bg-primary w-100 d-flex justify-content-center align-items-center" style="min-height: 150px;">
        <div class="footer-error-code"></div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>