<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <style>
        #video-container { position: relative; width: 640px; height: 480px; background: #000; }
        video { width: 100%; height: 100%; }
        #canvas { display: none; } /* Hidden canvas for capture */
    </style>
</head>
<body>
    <h1>Camera Editor</h1>
    
    <div id="stickers">
        <h3>1. Select a Sticker</h3>
        <div class="sticker-list" style="display:flex; gap:10px; margin-bottom: 20px;">
            <?php if (isset($stickers)): ?>
                <?php foreach ($stickers as $sticker): ?>
                    <img src="<?= htmlspecialchars($sticker) ?>" class="sticker-option" style="width:50px; cursor:pointer; border: 2px solid transparent;" onclick="selectSticker(this, '<?= $sticker ?>')">
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <input type="hidden" id="selected-sticker" name="sticker">
    </div>

    <div id="video-container">
        <video id="video" autoplay playsinline></video>
        <img id="sticker-overlay" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 30%; display: none; pointer-events: none;">
    </div>
    <div class="controls">
        <button id="capture-btn" disabled>Take Photo (Select Sticker First)</button>
        <button id="save-btn" style="display:none;">Save to Gallery</button>
    </div>
    
    <!-- Upload Fallback -->
    <div style="margin-top: 20px; border-top: 1px solid #ccc; padding-top: 10px;">
        <h3>Or Upload Image</h3>
        <form action="/camera/upload" method="POST" enctype="multipart/form-data">
            <input type="file" name="image" accept="image/*" required>
            <input type="hidden" id="upload-sticker" name="sticker">
            <button type="submit" id="upload-btn" disabled>Upload (Select Sticker First)</button>
        </form>
    </div>

    <canvas id="canvas" width="640" height="480"></canvas>
    
    <div id="preview">
        <h3>Preview</h3>
        <?php if (isset($uploaded_image)): ?>
            <img id="photo" src="<?= htmlspecialchars($uploaded_image) ?>" alt="Uploaded image" style="max-width: 640px;">
            <script>document.getElementById('save-btn').style.display = 'inline-block';</script>
        <?php else: ?>
            <img id="photo" alt="The screen capture will appear in this box.">
        <?php endif; ?>
    </div>
    
    <div id="gallery" style="margin-top: 40px; border-top: 2px solid #333; padding-top: 20px;">
        <h3>My Gallery</h3>
        <div style="display: flex; flex-wrap: wrap; gap: 10px;">
            <?php if (isset($userImages) && !empty($userImages)): ?>
                <?php foreach ($userImages as $img): ?>
                    <div style="border: 1px solid #ccc; padding: 5px;">
                        <img src="<?= htmlspecialchars($img['path']) ?>" style="width: 150px; height: auto;">
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No images saved yet.</p>
            <?php endif; ?>
        </div>
    </div>
        <?php if (isset($error)): ?>
            <p style="color:red"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
    </div>

    <script>
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const photo = document.getElementById('photo');
        const captureBtn = document.getElementById('capture-btn');
        const saveBtn = document.getElementById('save-btn');
        const stickerOverlay = document.getElementById('sticker-overlay');
        const selectedStickerInput = document.getElementById('selected-sticker');
        const uploadStickerInput = document.getElementById('upload-sticker');
        const uploadBtn = document.getElementById('upload-btn');

        function selectSticker(img, src) {
            // Highlight selection
            document.querySelectorAll('.sticker-option').forEach(el => el.style.border = '2px solid transparent');
            img.style.border = '2px solid blue';

            // Set inputs
            selectedStickerInput.value = src;
            uploadStickerInput.value = src;

            // Enable buttons
            captureBtn.disabled = false;
            uploadBtn.disabled = false;
            captureBtn.textContent = "Take Photo";
            uploadBtn.textContent = "Upload";

            // Show overlay on video
            stickerOverlay.src = src;
            stickerOverlay.style.display = 'block';
        }

        // Access Webcam
        async function initCamera() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
                video.srcObject = stream;
            } catch (err) {
                console.error("An error occurred: " + err);
                // Don't alert here to avoid annoying popups if no cam
            }
        }

        // Capture Photo
        captureBtn.addEventListener('click', () => {
            const context = canvas.getContext('2d');
            if (video.videoWidth && video.videoHeight) {
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                context.drawImage(video, 0, 0, video.videoWidth, video.videoHeight);
                
                // Note: We don't draw the sticker on canvas here because requirements say PHP GD must do it.
                // But for better UX we could, but let's stick to the rules: Server Side Merging.
                
                const data = canvas.toDataURL('image/png');
                photo.setAttribute('src', data);
                saveBtn.style.display = 'inline-block';
            }
        });
        
        // Save Photo
        saveBtn.addEventListener('click', () => {
            const dataUrl = photo.getAttribute('src');
            const sticker = selectedStickerInput.value;
            
            if (!dataUrl) return;
            if (!sticker) {
                alert("Please select a sticker first.");
                return;
            }

            fetch('/camera/save', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ image: dataUrl, sticker: sticker })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Image saved!');
                    // Update preview to show the merged image returned from server
                    photo.setAttribute('src', data.image_url);
                } else {
                    alert('Failed to save image.');
                }
            })
            .catch(err => console.error(err));
        });

        window.addEventListener('load', initCamera);
    </script>
    <p><a href="/">Back to Home</a></p>
</body>
</html>
