<?php

// Load autload.php file
require dirname(__DIR__) . "/vendor/autoload.php";

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);

    $username = $input["username"] ?? null;
    $password = $input["password"] ?? null;

    if (!$username || !$password) {
        http_response_code(400);
        echo json_encode(["error" => "Username and password required"]);
        exit;
    }

    if (strlen($username) > 0 && strlen($password) > 0) {
        $jwt = new JWTCodec($_ENV["JWT_SECRET"]);

        $payload = [
            "user" => $username,
            "exp" => time() + 3600
        ];

        $token = $jwt->encode($payload);

        header('Content-Type: application/json');
        echo json_encode([
            "access_token" => $token
        ]);
        exit;
    } else {
        http_response_code(401);
        echo json_encode(["error" => "Invalid credentials"]);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>JWT Login</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css" />
  <style>
    #tokenBox {
      word-break: break-all;
      cursor: pointer;
      user-select: all;
    }
  </style>
</head>
<body>
  <main class="container">
    <h1>Login to get your JWT</h1>

    <form id="loginForm">
      <label>
        Username
        <input type="text" name="username" required />
      </label>
      <label>
        Password
        <input type="password" name="password" required />
      </label>
      <button type="submit">Login</button>
    </form>

    <article id="result" style="display:none;">
      <h4>Your Token</h4>
      <p id="tokenBox" title="Click to copy"></p>
      <small>Saved to localStorage</small>
    </article>
  </main>

  <script>
    const form = document.getElementById("loginForm");
    const resultBox = document.getElementById("result");
    const tokenBox = document.getElementById("tokenBox");

    form.addEventListener("submit", async (e) => {
      e.preventDefault();
      const data = new FormData(form);
      const payload = {
        username: data.get("username"),
        password: data.get("password")
      };

      try {
        const res = await fetch("", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(payload)
        });

        // Get raw response text
        const text = await res.text();
        console.log(text);
        const json = JSON.parse(text);

        if (res.ok && json.access_token) {
          const token = json.access_token;
          tokenBox.textContent = token;
          localStorage.setItem("jwt", token);
          resultBox.style.display = "block";
        } else {
          alert(json.error || "Login failed");
        }

      } catch (error) {
        console.error("Error parsing JSON:", error);
        alert("There was an error processing the login.");
      }
    });

    tokenBox.addEventListener("click", () => {
      navigator.clipboard.writeText(tokenBox.textContent)
        .then(() => alert("Copied to clipboard ✅"));
    });
  </script>
</body>
</html>
