<?php include('../../logins/auth.php'); ?>
<!DOCTYPE html>
<html>
<head>
    <title>Update Playlist Cover</title>
    <link rel="stylesheet" href="https://unpkg.com/cropperjs@1.5.13/dist/cropper.min.css"/>
    <style>
        body {
            background: #121212;
            color: #fff;
            font-family: 'Segoe UI', sans-serif;
            padding: 40px;
            text-align: center;
        }
        input, button {
            margin-top: 15px;
            padding: 10px;
            border: none;
            border-radius: 6px;
        }
        button {
            background-color: #1db954;
            color: white;
            cursor: pointer;
        }
        #image {
            max-width: 100%;
            max-height: 400px;
            margin-top: 20px;
            display: none;
        }
    </style>
</head>
<body>

<h2>Upload & Crop Playlist Cover</h2>
<input type="file" id="coverInput" accept="image/*">
<input type="hidden" id="playlistId" value="<?= htmlspecialchars($_GET['playlist_id']) ?>">
<br>
<canvas id="preview" style="display:none;"></canvas>
<img id="image">
<br>
<button onclick="uploadCroppedImage()">Upload Cover</button>

<script src="https://unpkg.com/cropperjs@1.5.13/dist/cropper.min.js"></script>
<script>
    let cropper;
    const input = document.getElementById('coverInput');
    const image = document.getElementById('image');

    input.addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function (event) {
            image.src = event.target.result;
            image.style.display = 'block';

            if (cropper) cropper.destroy();
            cropper = new Cropper(image, {
                aspectRatio: 1,
                viewMode: 1,
                autoCropArea: 1
            });
        };
        reader.readAsDataURL(file);
    });

    function uploadCroppedImage() {
        if (!cropper) return alert("Crop your image first.");

        cropper.getCroppedCanvas({ width: 500, height: 500 }).toBlob(function (blob) {
            const formData = new FormData();
            formData.append("cropped_image", blob);
            formData.append("playlist_id", document.getElementById('playlistId').value);

            fetch("update_playlist_cover_handler.php", {
                method: "POST",
                body: formData
            })
            .then(res => res.text())
            .then(msg => {
                // alert(msg);
                window.location.href = "../beatdrop.html";
            });
        }, 'image/jpeg');
    }
</script>
</body>
</html>
