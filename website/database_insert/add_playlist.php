<?php include('../../logins/auth.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Create Playlist</title>
  <link href="https://unpkg.com/cropperjs@1.5.13/dist/cropper.min.css" rel="stylesheet"/>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #121212;
      color: #fff;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding-top: 40px;
    }
    h2 {
      font-size: 32px;
      color: #1db954;
    }
    form {
      background: #181818;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 0 10px rgba(0,0,0,0.6);
      width: 320px;
    }
    input[type="text"], textarea, input[type="file"] {
      width: 100%;
      padding: 10px;
      margin: 10px 0;
      border: none;
      border-radius: 6px;
      background: #282828;
      color: white;
    }
    textarea {
      resize: none;
      height: 70px;
    }
    .btn {
      padding: 10px 20px;
      margin-top: 15px;
      border: none;
      background: #1db954;
      color: white;
      border-radius: 6px;
      font-weight: bold;
      cursor: pointer;
      width: 100%;
    }
    .btn:hover {
      background: #1ed760;
    }
    #cropModal {
      display: none;
      position: fixed;
      z-index: 999;
      left: 0; top: 0;
      width: 100vw; height: 100vh;
      background: rgba(0, 0, 0, 0.85);
      justify-content: center;
      align-items: center;
    }
    #cropBox {
      background: #181818;
      padding: 20px;
      border-radius: 12px;
      text-align: center;
    }
    #imageToCrop {
      max-width: 300px;
      max-height: 60vh;
      margin-bottom: 15px;
    }
    .crop-btn {
      background: #1db954;
      margin: 5px;
    }
  </style>
</head>
<body>
  <h2>Create Playlist</h2>
  <form id="playlistForm" action="add_playlist_handler.php" method="POST" onsubmit="return validateBeforeSubmit();">
    <input type="text" name="name" id="playlistName" placeholder="Playlist Name (max 16 chars)" maxlength="16" required>
    <textarea name="description" id="playlistDesc" placeholder="Playlist Description (max 75 chars)" maxlength="75"></textarea>
    <input type="file" id="image" accept="image/*"> <!-- image is now optional -->
    <input type="hidden" name="cropped_image" id="croppedImageData">
    <button type="submit" class="btn">Create Playlist</button>
  </form>
 
  <div id="cropModal">
    <div id="cropBox">
      <h3 style="color: #1db954;">Crop Playlist Cover</h3>
      <img id="imageToCrop" />
      <br>
      <button class="btn crop-btn" onclick="cropImage()">Crop & Use</button>
      <button class="btn" onclick="closeCropper()">Cancel</button>
    </div>
  </div>

  <script src="https://unpkg.com/cropperjs@1.5.13/dist/cropper.min.js"></script>
  <script>
    const imageInput = document.getElementById('image');
    const imageToCrop = document.getElementById('imageToCrop');
    const cropModal = document.getElementById('cropModal');
    const croppedImageData = document.getElementById('croppedImageData');
    let cropper;

    imageInput.addEventListener('change', function () {
      const file = this.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
          imageToCrop.src = e.target.result;
          cropModal.style.display = 'flex';
          if (cropper) cropper.destroy();
          cropper = new Cropper(imageToCrop, {
            aspectRatio: 1,
            viewMode: 1,
          });
        };
        reader.readAsDataURL(file);
      }
    });

    function cropImage() {
      const canvas = cropper.getCroppedCanvas({ width: 300, height: 300 });
      canvas.toBlob(blob => {
        const reader = new FileReader();
        reader.onloadend = () => {
          croppedImageData.value = reader.result;
          cropModal.style.display = 'none';
        };
        reader.readAsDataURL(blob);
      }, 'image/jpeg');
    }

    function closeCropper() {
      cropModal.style.display = 'none';
      imageInput.value = ''; // clear input
      cropper?.destroy();
    }

    function validateBeforeSubmit() {
      const name = document.getElementById('playlistName').value.trim();
      const desc = document.getElementById('playlistDesc').value.trim();

      if (name.length > 16) {
        alert("❌ Playlist name must be max 16 characters.");
        return false;
      }
      if (desc.length > 75) {
        alert("❌ Description must be max 75 characters.");
        return false;
      }

      // image not required anymore
      return true;
    }
  </script>
</body>
</html>
