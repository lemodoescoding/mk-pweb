<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin - Job Seeder</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="/css/styleAdmin.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

  <style>
    :root {
      --sidebar-width: 250px;
      --blue-primary: #3b82f6;
      --content-bg: #f7f7f7;
    }

    html,
    body {
      min-height: 100vh;
      margin: 0;
      background-color: var(--content-bg);
    }

    .main-layout {
      display: flex;
      min-height: 100vh;
    }

    /* Simple Sidebar placeholder styles */
    .sidebar {
      width: var(--sidebar-width);
      background-color: #ffffff;
      border-right: 1px solid #e0e0e0;
      position: sticky;
      top: 0;
      padding: 20px;
    }

    /* Content area styles */
    .content {
      flex-grow: 1;
      padding: 40px;
      width: calc(100% - var(--sidebar-width));
    }

    .seeder-card {
      max-width: 800px;
      margin: 0 auto;
      background-color: white;
      padding: 30px;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    .code-input {
      font-family: monospace;
      min-height: 300px;
      background-color: #f8f9fa;
      border: 1px solid #ced4da;
    }

    #seeder-log {
      font-family: monospace;
      white-space: pre-wrap;
      background-color: #333;
      color: #0f0;
      padding: 15px;
      border-radius: 4px;
      margin-top: 20px;
    }

    .btn-seed {
      background-color: var(--blue-primary);
      color: white;
      border: none;
    }
  </style>
</head>

<body>

  <div class="main-layout">

    <aside class="sidebar">
      <h5 class="fw-bold mb-4">Admin Panel</h5>
      <a href="/admin" class="d-block mb-2 text-decoration-none text-secondary"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>
      <a href="#" class="d-block mb-2 text-decoration-none text-primary fw-bold"><i class="fas fa-database me-2"></i> Job Seeder</a>
    </aside>

    <main class="content">
      <div class="seeder-card">
        <h3 class="fw-bold mb-4 text-center">External Job Seeder Tool</h3>

        <div class="alert alert-info" role="alert">
          Enter the job seeding parameters as a **valid JSON object** below.
          <br>
          <small class="text-muted">
            *Required: `categories`, `countries`, `pages`. `offset_page` is optional.
          </small>
        </div>

        <form id="seeder-form">
          <div class="mb-3">
            <label for="json-input" class="form-label fw-bold">JSON Input Payload</label>
            <textarea class="form-control code-input" id="json-input" name="payload" rows="10" required>
{
    "categories": {
        "software engineer": ["san francisco", "berlin"],
        "marketing analyst": ["new york"]
    },
    "countries": {
        "san francisco": "us",
        "berlin": "de",
        "new york": "us"
    },
    "pages": 3,
    "offset_page": 2
}</textarea>
          </div>

          <div class="d-grid gap-2">
            <button type="submit" class="btn btn-seed btn-lg" id="seed-button">
              <i class="fas fa-play me-2"></i> Run Job Seeder
            </button>
          </div>
        </form>

        <div id="status-message" class="mt-3" style="display:none;"></div>

        <div id="seeder-log-container" style="display:none;">
          <h4 class="mt-4">Seeder Output Log:</h4>
          <pre id="seeder-log">Waiting for execution...</pre>
        </div>
      </div>
    </main>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function getCookie(name) {
      const cookies = document.cookie.split("; ");
      const cookieMap = {};

      cookies.forEach((cookie) => {
        const [name, value] = cookie.split("=");
        cookieMap[name] = value;
      });

      return cookieMap[name];
    }

    const form = document.getElementById('seeder-form');
    const jsonInput = document.getElementById('json-input');
    const seedButton = document.getElementById('seed-button');
    const statusMessage = document.getElementById('status-message');
    const logContainer = document.getElementById('seeder-log-container');
    const seederLog = document.getElementById('seeder-log');

    const API_ENDPOINT = '/api/jobs/populate';

    form.addEventListener('submit', async (e) => {
      e.preventDefault();

      statusMessage.style.display = 'none';
      logContainer.style.display = 'block';
      seederLog.textContent = 'Starting job seeder...';
      seedButton.disabled = true;
      seedButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Running...';

      let payload;
      try {
        payload = JSON.parse(jsonInput.value);
        seederLog.textContent += '\n[OK] JSON parsed successfully.';

        if (typeof payload.countries === 'undefined') {
          throw new Error("Missing 'countries' field. Please add it to the JSON.");
        }

        if (typeof payload.categories === 'undefined') {
          throw new Error("Missing 'categories' field. Please add it to the JSON.");
        }

        if (typeof payload.pages === 'undefined') {
          throw new Error("Missing 'pages' field. Please add it to the JSON.");
        }
      } catch (error) {
        seederLog.textContent += `\n[ERROR] JSON Parsing Failed: ${error.message}`;
        statusMessage.className = 'alert alert-danger';
        statusMessage.textContent = 'JSON Input Error. Check console log.';
        statusMessage.style.display = 'block';

        seedButton.disabled = false;
        seedButton.innerHTML = '<i class="fas fa-play me-2"></i> Run Job Seeder';
        return;
      }

      try {
        seederLog.textContent += `\nSending request to ${API_ENDPOINT}...`;

        const response = await fetch(API_ENDPOINT, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + getCookie('api_token')
          },
          body: JSON.stringify(payload)
        });

        const result = await response.json();

        // 3. Process API Response
        if (response.ok) {
          statusMessage.className = 'alert alert-success';
          statusMessage.textContent = result.message || 'Job Seeding Completed Successfully!';
          seederLog.textContent += `\n[SUCCESS] API Response: ${result.message}`;
          seederLog.textContent += '\n\nFull Response Data:\n' + JSON.stringify(result.data, null, 2);
        } else {
          statusMessage.className = 'alert alert-danger';
          statusMessage.textContent = result.message || 'Job Seeding Failed. See log for details.';
          seederLog.textContent += `\n[FAILURE] Status: ${response.status}. Message: ${result.message}`;
          seederLog.textContent += '\n\nFull Error Response:\n' + JSON.stringify(result, null, 2);
        }

      } catch (error) {
        statusMessage.className = 'alert alert-danger';
        statusMessage.textContent = 'A network error occurred. Check server logs.';
        seederLog.textContent += `\n[FATAL ERROR] Network/Server Error: ${error.message}`;
      } finally {
        seedButton.disabled = false;
        seedButton.innerHTML = '<i class="fas fa-play me-2"></i> Run Job Seeder';
        statusMessage.style.display = 'block';
      }
    });

    document.addEventListener('DOMContentLoaded', () => {
      const jsonInput = document.getElementById('json-input');

      jsonInput.innerHTML = `{
  "categories": {
    "software engineer": ["san francisco", "berlin"],
    "marketing analyst": ["new york"]
  },
  "countries": {
    "san francisco": "us",
    "berlin": "de",
    "new york": "us"
  },
  "pages": 3,
  "offset_page": 2
}`
    });
  </script>
</body>

</html>
