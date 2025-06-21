// Global variables
let currentSong = new Audio();
let songs = [];
let currentSongIndex = 0;
let currentTrackId = null;


// Time formatting function
function formatTime(seconds) {
    if (isNaN(seconds) || seconds < 0) {
        return "00:00";
    }
    let totalSeconds = Math.round(seconds);
    let minutes = Math.floor(totalSeconds / 60);
    let secs = totalSeconds % 60;
    minutes = minutes < 10 ? '0' + minutes : minutes;
    secs = secs < 10 ? '0' + secs : secs;
    return minutes + ':' + secs;
}

// Add event listener to previous and next buttons
function addEventListenersToControlButtons() {
    const playPrevious = () => {
        if (songs.length === 0) return;

        currentSongIndex = (currentSongIndex - 1 + songs.length) % songs.length;
        playMusic(songs[currentSongIndex]);
    };

    const playNext = () => {
        if (songs.length === 0) return;

        currentSongIndex = (currentSongIndex + 1) % songs.length;
        playMusic(songs[currentSongIndex]);
    };

    document.getElementById("previous").addEventListener("click", playPrevious);
    document.getElementById("next").addEventListener("click", playNext);

    document.addEventListener("keydown", (event) => {
        switch (event.key) {
            case "ArrowLeft":
                currentSong.currentTime = Math.max(currentSong.currentTime - 5, 0);
                break;
            case "ArrowRight":
                currentSong.currentTime = Math.min(currentSong.currentTime + 10, currentSong.duration);
                break;
            case "ArrowDown":
                playNext();
                break;
            case "ArrowUp":
                playPrevious();
                break;
        }
    });
}

async function checkIfLiked(trackId) {
    const formData = new FormData();
    formData.append("track_id", trackId);

    const res = await fetch("database_fetch/check_like.php", {
        method: "POST",
        body: formData,
    });
    const data = await res.json();

    const heart = document.getElementById("heart-icon");
    heart.classList.toggle("heart-filled", data.liked);
}

async function getSongs(playlistIdOrSearch) {
    try {
        const isNumeric = /^\d+$/.test(playlistIdOrSearch);
        const url = isNumeric
            ? `database_fetch/get_songs.php?playlist_id=${playlistIdOrSearch}`
            : `database_fetch/search_songs.php?search=${encodeURIComponent(playlistIdOrSearch)}`;

        const response = await fetch(url);
        const fetchedSongs = await response.json();
        currentSongIndex = 0;

        if (!Array.isArray(fetchedSongs)) throw new Error("Invalid songs data");

        songs = fetchedSongs; // ✅ FIX: update global array here

        const songUL = document.querySelector(".songList ul");
        songUL.innerHTML = "";

        fetchedSongs.forEach(song => {
            const { track_name, artist_name, path, duration, track_id } = song;
            songUL.innerHTML += `
                <li>
                    <img class="invert" src="assets/svgs/music.svg" alt="music icon">
                    <div class="info">
                        <div class="mySongName">${track_name}</div>
                        <div>${artist_name}</div>
                    </div>
                    <div class="playnow">
                        <span>Play Now</span>
                        <img class="invert" height="22px" src="assets/svgs/playnow.svg" alt="play icon">
                    </div>
                </li>
            `;
        });

        Array.from(songUL.getElementsByTagName("li")).forEach((li, index) => {
            li.addEventListener("click", () => {
                const selectedSong = fetchedSongs[index];
                playMusic(selectedSong);
                currentTrackId = selectedSong.track_id;
                currentSongIndex = index; // ✅ Add this to keep track of the index!
                checkIfLiked(currentTrackId);
            });
        });

    } catch (error) {
        console.error("Error loading songs:", error);
    }
}

// Music playing function
const playMusic = (songObj, pause = false) => {
    const { path, track_name, artist_name } = songObj;

    currentSong.src = path;

    if (!pause) {
        currentSong.play();
    }

    document.getElementById("play-wrapper").innerHTML = `
        <lord-icon 
            style="position:relative; scale:1.2; bottom:2px; width:33px; height:33px;" 
            src="https://cdn.lordicon.com/jctchmfs.json" 
            trigger="hover" 
            colors="primary:#ffffff">
        </lord-icon>
    `;

    document.querySelector(".songinfo").innerHTML = `${track_name} - ${artist_name}`;
    document.querySelector(".songtime").innerHTML = "00:00 / 00:00";
};

// popup via iframes
function openPopup(url) {
    document.getElementById("popupFrame").src = url;
    document.getElementById("popupOverlay").classList.remove("hidden");
}

function closePopup() {
    document.getElementById("popupFrame").src = "";
    document.getElementById("popupOverlay").classList.add("hidden");
}


// Function to display albums from local data
async function displayAlbums() {
    let cardContainer = document.querySelector(".cardContainer");
    cardContainer.innerHTML = `
                    <div data-folder="BeatSense" class="card">
                        <div class="play">
                            <img src="https://img.icons8.com/sf-black-filled/64/play.png" alt="play" />
                        </div>
                        <img src="assets/Default_Albums/BeatSense.jpeg" alt="cover pic">
                        <h2>BEATSENSE</h2>
                        <p>AI-curated rhythms that vibe with your mood — discover your sound with BeatSense.</p>
                    </div>
                    <div data-folder="Add" class="card">
                        <img src="assets/Default_Albums/Add.jpeg" alt="cover pic">
                        <h2>Update Panel</h2>
                        <p>Add or update tracks, playlists, artists, admins and more...</p>
                    </div>
    `;

    try {
        const response = await fetch("database_fetch/get_playlists.php");
        const albums = await response.json();

        if (!Array.isArray(albums)) throw new Error("Invalid JSON format");

        albums.forEach(album => {
            cardContainer.innerHTML = `
                <div data-folder="${album.folder}" data-id="${album.playlist_id}" class="card">
                    <div class="play">
                        <img src="https://img.icons8.com/sf-black-filled/64/play.png" alt="play" />
                    </div>
                    <img src="${album.image_url}" alt="cover pic">
                    <h2>${album.title}</h2>
                    <p>${album.description}</p>
                </div>` + cardContainer.innerHTML;
        });

        document.querySelectorAll(".card").forEach(card => {
            card.addEventListener("click", async () => {
                const folder = card.dataset.folder;
                if (folder === "BeatSense") {
                    // window.location.href = "BeatSense/BeatSense.html";
                    openPopup("BeatSense/BeatSense.html")
                } else if (folder === "Add") {
                    openPopup('database_insert/add.html')
                } else {
                    await getSongs(card.dataset.id);
                }
            });
        });

    } catch (error) {
        console.error("Error loading albums:", error);
    }
}


// Inside main function
async function main() {
    songs = await getSongs('17');

    displayAlbums();

    addEventListenersToControlButtons();

    // Song play pause on click
    const playWrapper = document.getElementById("play-wrapper");
    playWrapper.addEventListener("click", () => {
        if (currentSong.paused) {
            currentSong.play();
            playWrapper.innerHTML = `<lord-icon style="position:relative; scale:1.2; bottom: 2px;" src="https://cdn.lordicon.com/jctchmfs.json" trigger="hover" colors="primary:#ffffff" style="width:33px;height:33px"></lord-icon>`;
        } else {
            currentSong.pause();
            playWrapper.innerHTML = `<img id="play" src="assets/svgs/play.svg" alt="play">`;
        }
    });

    // Song play pause on space bar
    document.addEventListener('keydown', function (event) {
        if (event.code === 'Space') {
            if (currentSong.paused) {
                currentSong.play();
                playWrapper.innerHTML = `<lord-icon style="position:relative; scale:1.2; bottom: 2px;" src="https://cdn.lordicon.com/jctchmfs.json" trigger="hover" colors="primary:#ffffff" style="width:33px;height:33px"></lord-icon>`;
            } else {
                currentSong.pause();
                playWrapper.innerHTML = `<img id="play" src="assets/svgs/play.svg" alt="play">`;
            }
        }
    });

    // Listen for time update
    currentSong.addEventListener("timeupdate", () => {
        document.querySelector(".songtime").innerHTML = `${formatTime(currentSong.currentTime)} / ${formatTime(currentSong.duration)}`;
        document.querySelector(".circle").style.left = (currentSong.currentTime / currentSong.duration) * 100 + "%";
    });

    // Add an event listener to seekbar
    document.querySelector(".seekbar").addEventListener("click", e => {
        let percent = (e.offsetX / e.target.getBoundingClientRect().width) * 100;
        let percentage = percent - 1;
        document.querySelector(".circle").style.left = percentage + "%";
        currentSong.currentTime = (currentSong.duration) * percent / 100;
    });


    // Add click event listener to the hamburger icon
    document.querySelector(".hamburger").addEventListener("click", () => {
        document.querySelector(".left").style.left = "0";
    });


    // Add touch event listeners for swipe left
    let xDown = null;

    document.querySelector(".right").addEventListener('touchstart', handleTouchStart, false);
    document.querySelector(".right").addEventListener('touchmove', handleTouchMove, false);

    function handleTouchStart(event) {
        const firstTouch = event.touches[0];
        xDown = firstTouch.clientX;
    }

    function handleTouchMove(event) {
        if (!xDown) {
            return;
        }

        let xUp = event.touches[0].clientX;
        let xDiff = xDown - xUp;

        if (xDiff > 0) {
            // Swipe left detected
            document.querySelector(".left").style.left = "-120%";
        }
        else if (xDiff < 0) {
            // Swipe left detected
            document.querySelector(".left").style.left = "-120%";
        }
        // Reset xDown
        xDown = null;
    }

    // Add event listener to cards only when screen width is below 1300px
    if (window.matchMedia("(max-width: 1300px)").matches) {
        Array.from(document.querySelectorAll(".card")).forEach(card => {
            card.addEventListener("click", () => {
                document.querySelector(".left").style.left = "0";
            });
        });
    }

    document.querySelector(".close").addEventListener("click", () => {
        document.querySelector(".left").style.left = "-120%";
    });

    document.querySelector(".signupbtn").addEventListener("click", () => {
        window.location.href = "../index.html";
    });

    // Assuming currentSong is your audio element
    currentSong.addEventListener('timeupdate', function () {
        if (currentSong.currentTime >= currentSong.duration) {
            document.getElementById("next").click();
        }
    });

    // Toggle like when heart is clicked
    document.querySelector(".like-button").addEventListener("click", async () => {
        console.log("Like button clicked");
        if (!currentTrackId) return;

        const formData = new FormData();
        formData.append("track_id", currentTrackId);

        try {
            const res = await fetch("database_fetch/toggle_like.php", {
                method: "POST",
                body: formData,
                credentials: "include", // important to send session cookie!
            });
            const data = await res.json();
            if (data.success) {
                const heart = document.getElementById("heart-icon");
                heart.classList.toggle("heart-filled", data.liked);
            } else {
                console.error("Failed to toggle like:", data.message);
            }
        } catch (err) {
            console.error("Error toggling like:", err);
        }
    });

    // Check if current song is liked
    async function checkIfLiked(trackId) {
        const formData = new FormData();
        formData.append("track_id", trackId);

        const res = await fetch("database_fetch/check_like.php", {
            method: "POST",
            body: formData,
        });
        const data = await res.json();

        const heart = document.getElementById("heart-icon");
        heart.classList.toggle("heart-filled", data.liked);
    }

    // Check if current song is liked
    document.querySelector(".songinfo").addEventListener("click", () => {
        const text = document.querySelector(".songinfo").textContent;
        const artistName = text.split(" - ")[1]?.trim();

        if (artistName) {
            const urlEncodedArtist = encodeURIComponent(artistName);
            // window.location.href = `database_insert/artist_followers.php?artist=${urlEncodedArtist}`;
            openPopup(`database_insert/artist_followers.php?artist=${urlEncodedArtist}`)
        }
    });

    const searchBtn = document.getElementById("search");
    const searchPopup = document.getElementById("searchPopup");
    const searchInput = document.getElementById("popupSearchInput");
    // Show popup on click
    searchBtn.addEventListener("click", () => {
        // Show
        document.getElementById("searchPopup").classList.remove("hidden");
        document.getElementById("searchPopup").classList.add("show");
        searchInput.focus();
    });

    // Hide
    document.getElementById("closeSearchPopup").addEventListener("click", function () {
        const popup = document.getElementById("searchPopup");
        popup.classList.remove("show");
        popup.classList.add("hidden");
    });

    // live search
    searchInput.addEventListener("input", async () => {
        const query = searchInput.value.trim();
        if (query.length > 0) {
            await getSongs(query);
        }
    });

    // Monitor iframe navigation and auto-close if necessary
    document.getElementById("popupFrame").addEventListener("load", function () {
        try {
            const frameUrl = this.contentWindow.location.href;

            // Replace this condition with your BeatDrop page or disallowed pages
            if (frameUrl.includes("beatdrop.html") || frameUrl.includes("index.html")) {
                closePopup();
                location.reload();
            }
        } catch (error) {
            // Cross-origin error fallback
            console.warn("Cannot access iframe URL due to cross-origin restrictions.");
        }
    });

}

// Run main function when the document is ready
document.addEventListener("DOMContentLoaded", main);