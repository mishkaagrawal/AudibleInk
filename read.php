<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['file'])) {
    die("No file specified.");
}

$file_name = basename(urldecode($_GET['file']));
$full_path = __DIR__ . "/uploads/" . $file_name;

if (!file_exists($full_path)) {
    die("File not found.");
}

$file_extension = strtolower(pathinfo($full_path, PATHINFO_EXTENSION));
$text_content = "";

if ($file_extension === "txt") {
    $text_content = file_get_contents($full_path);
} elseif ($file_extension !== "pdf") {
    die("Unsupported file format.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Read Aloud | AudibleInk</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #fefae0;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        #timer {
            text-align: center;
            margin-top: 20px;
            font-weight: bold;
            color: #d72638;
            font-size: 18px;
        }

        .document-container {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            margin: 20px;
            overflow-y: auto;
        }

        .pdf-view, .text-view {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            max-width: 80%;
            max-height: 500px;
            overflow-y: auto;
        }

        .control-bar {
            position: fixed;
            bottom: 0;
            width: 100%;
            background: #ffafcc;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
            padding: 10px;
            box-shadow: 0 -2px 8px rgba(0,0,0,0.1);
            flex-wrap: wrap;
        }

        .control-bar button, .control-bar select, .control-bar a {
            padding: 8px 12px;
            font-size: 14px;
            border-radius: 6px;
            border: none;
            font-weight: bold;
            cursor: pointer;
        }

        .control-bar a {
            background: white;
            color: #d72638;
            text-decoration: none;
        }

        .control-bar button:hover, .control-bar select:hover {
            background-color: #fcd5ce;
        }

        canvas {
            display: block;
            margin: auto;
            width: 100% !important;
            height: auto !important;
        }

        #pdf-text { display: none; }
    </style>
</head>
<body>

<div id="timer">⏳ Time Spent: 00:00:00</div>

<div class="document-container">
    <?php if ($file_extension === "txt"): ?>
        <div class="text-view">
            <p id="document-content"><?php echo nl2br(htmlspecialchars($text_content)); ?></p>
        </div>
    <?php else: ?>
        <div class="pdf-view" id="pdf-container"></div>
        <p id="pdf-text"></p>
    <?php endif; ?>
</div>

<div class="control-bar">
    <a href="library.php">🔙 Back</a>
    <button onclick="playAudio()">▶️ Play</button>
    <button onclick="pauseAudio()">⏸️ Pause</button>
    <select id="speedControl">
        <option value="slow">🐢 Slow</option>
        <option value="normal" selected>🚶 Normal</option>
        <option value="fast">🐇 Fast</option>
    </select>
    <select id="voiceControl">
        <option value="male">👨 Male</option>
        <option value="female" selected>👩 Female</option>
    </select>
</div>

<audio id="ttsAudio" controls hidden></audio>

<script>
    const audio = document.getElementById("ttsAudio");
    let timer = 0, timerInterval;

    function startTimer() {
        timerInterval = setInterval(() => {
            timer++;
            let hrs = String(Math.floor(timer / 3600)).padStart(2, '0');
            let mins = String(Math.floor((timer % 3600) / 60)).padStart(2, '0');
            let secs = String(timer % 60).padStart(2, '0');
            document.getElementById("timer").innerText = `⏳ Time Spent: ${hrs}:${mins}:${secs}`;
        }, 1000);
    }

    function stopTimer() {
        clearInterval(timerInterval);
    }

    function playAudio() {
        const textElement = document.getElementById("document-content") || document.getElementById("pdf-text");
        const text = textElement.innerText.trim();
        if (!text) return alert("No content to read.");

        const speed = document.getElementById("speedControl").value;
        const voice = document.getElementById("voiceControl").value;

        fetch("http://127.0.0.1:5000/speak", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ text, speed, voice })
        })
        .then(res => res.blob())
        .then(blob => {
            const url = URL.createObjectURL(blob);
            audio.src = url;
            audio.play();
            startTimer();
        })
        .catch(err => {
            console.error("Error fetching audio:", err);
            alert("Failed to fetch audio from server.");
        });
    }

    function pauseAudio() {
        audio.pause();
        stopTimer();
    }

    document.addEventListener("keydown", function(e) {
        if (e.code === "Space") {
            e.preventDefault();
            if (audio.paused) {
                audio.play();
                startTimer();
            } else {
                pauseAudio();
            }
        }
    });

    <?php if ($file_extension === "pdf"): ?>
    const pdfUrl = "<?php echo htmlspecialchars("uploads/$file_name"); ?>";
    const pdfContainer = document.getElementById("pdf-container");

    pdfjsLib.getDocument(pdfUrl).promise.then(pdf => {
        const textArray = [];

        function renderPage(num) {
            pdf.getPage(num).then(page => {
                const viewport = page.getViewport({ scale: 1.2 });
                const canvas = document.createElement("canvas");
                const context = canvas.getContext("2d");
                canvas.width = viewport.width;
                canvas.height = viewport.height;

                page.render({ canvasContext: context, viewport: viewport });
                pdfContainer.appendChild(canvas);
            });
        }

        function extractText(num) {
            pdf.getPage(num).then(page => {
                page.getTextContent().then(content => {
                    const text = content.items.map(item => item.str).join(" ");
                    textArray.push(text);
                    if (num < pdf.numPages) extractText(num + 1);
                    else document.getElementById("pdf-text").innerText = textArray.join(" ");
                });
            });
        }

        for (let i = 1; i <= pdf.numPages; i++) renderPage(i);
        extractText(1);
    });
    <?php endif; ?>
</script>

</body>
</html>