<?php
session_start();

// Ensure file is provided
if (!isset($_GET['file'])) {
    die("No file specified.");
}

// Sanitize file path to prevent attacks
$file_name = basename(urldecode($_GET['file']));
$full_path = __DIR__ . "/uploads/" . $file_name;

if (!file_exists($full_path)) {
    die("File not found.");
}

// Get file extension
$file_extension = strtolower(pathinfo($full_path, PATHINFO_EXTENSION));

// Check user role
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$is_user = isset($_SESSION['role']) && $_SESSION['role'] === 'user';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Document | AudibleInk</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
    <style>
        body { font-family: 'Poppins', sans-serif; background: #fefae0; text-align: center; padding: 20px; }
        .document-container { 
            width: 80%; 
            margin: auto; 
            background: white; 
            padding: 10px; 
            border-radius: 10px; 
            text-align: left; 
            max-height: 80vh; 
            overflow-y: auto; 
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }
        .pdf-container { 
            width: 100%; 
            height: 90vh; /* Ensure PDF uses available screen space */
            display: flex; 
            justify-content: center; 
            align-items: center; 
        }
        .button { padding: 10px 20px; background: #ffafcc; color: white; border-radius: 8px; text-decoration: none; font-weight: 600; display: inline-block; margin: 5px; }
        .button:hover { background: #ff8fab; }
    </style>
</head>
<body>

<h1>📖 View Document</h1>

<div class="document-container">
    <?php
    if ($file_extension === "txt") {
        echo "<pre id='document-content'>" . htmlspecialchars(file_get_contents($full_path)) . "</pre>";
    } elseif ($file_extension === "pdf") {
        echo "<div class='pdf-container'><iframe src='uploads/" . htmlspecialchars($file_name) . "' width='90%' height='100%'></iframe></div>";
    } else {
        echo "<p>Unsupported file format. Please upload a .txt or .pdf file.</p>";
    }
    ?>
</div>

<a href="library.php" class="button">🔙 Back to Library</a>

<!-- Read Aloud button only for Users -->
<?php if ($is_user): ?>
    <a href="read.php?file=<?php echo urlencode($file_name); ?>" class="button">🎧 Read Aloud</a>
<?php endif; ?>

<script>
    <?php if ($file_extension === "pdf"): ?>
    let pdfUrl = "<?php echo htmlspecialchars("uploads/$file_name"); ?>";
    let container = document.getElementById("pdf-container");

    pdfjsLib.getDocument(pdfUrl).promise.then(pdf => {
        let totalPages = pdf.numPages;
        
        function renderPage(pageNumber) {
            pdf.getPage(pageNumber).then(page => {
                let canvas = document.createElement("canvas");
                container.appendChild(canvas);

                let viewport = page.getViewport({ scale: 1.5 });
                canvas.width = viewport.width;
                canvas.height = viewport.height;

                let context = canvas.getContext("2d");
                page.render({ canvasContext: context, viewport: viewport });
                
                // Extract text
                page.getTextContent().then(textContent => {
                    let text = textContent.items.map(item => item.str).join(" ");
                    document.getElementById("pdf-text").innerText += text + "\n\n";
                });

                if (pageNumber < totalPages) {
                    renderPage(pageNumber + 1);
                }
            });
        }

        renderPage(1); // Start rendering from page 1
    });
    <?php endif; ?>
</script>

</body>
</html>
