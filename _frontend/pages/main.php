<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Basixs | The Most Basic PHP Framework</title>
  <script src="https://cdn.tailwindcss.com"></script>

  <style>
    body {
      background-color: #0d0d0d;
      color: #00ff88;
      font-family: 'Courier New', Courier, monospace;
    }
    .glow {
      text-shadow: 0 0 5px #00ff88, 0 0 10px #00ff88, 0 0 20px #00ff88;
    }
  </style>
</head>
<body class="h-screen flex flex-col justify-center items-center text-center px-4">
  <div class="glow text-6xl md:text-7xl font-bold mb-4">
    Basixs
  </div>
  <p class="text-xl md:text-2xl text-green-300 mb-8">
    The most basic PHP framework — clean, fast and beginner friendly.
  </p>

  <div class="flex gap-4">
    <a href="/docs" class="bg-transparent border border-green-400 text-green-300 px-6 py-2 hover:bg-green-700 transition">
      Documentation
    </a>
    <a href="/download" class="bg-green-500 text-black px-6 py-2 font-semibold hover:bg-green-600 transition">
      Download
    </a>
  </div>

  <footer class="absolute bottom-4 text-green-500 text-sm opacity-60">
    &copy; <?= date('Y') ?> Basixs Framework. Built with ❤️ in PHP <br>by YROS.
  </footer>
</body>
</html>
