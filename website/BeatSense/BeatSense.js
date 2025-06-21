document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("recommendBtn").addEventListener("click", getRecommendations);
    document.addEventListener("keydown", function(e){
        if(e.key==="Enter"){
            getRecommendations();
        }
    });
});

async function getRecommendations() {
    const input = document.getElementById("musicInput").value.trim();
    const apiKey = getenv('BEATSENSE_API');

    if (!input) {
        alert("🎵 Yo DJ! Type something like an artist, genre or question!");
        return;
    }
    
    const prompt = `
    You're BeatSense, a fun and music-savvy AI. If someone asks about a song, artist, genre or musical history, answer like a cool DJ with a little humor.
    Always keep the answer helpful and friendly. Input: "${input}".
    `;

    try {
        const response = await fetch("https://api.openai.com/v1/chat/completions", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Authorization": `Bearer ${apiKey}`
            },
            body: JSON.stringify({
                model: "gpt-3.5-turbo",
                messages: [{ role: "user", content: prompt }]
            })
        });

        const data = await response.json();
        const answer = data.choices?.[0]?.message?.content || "Hmm... I drew a blank. Try again!";

        document.getElementById("recommendations").innerHTML = `
            <div class="card">
                <h2>🎧 BeatSense Says:</h2>
                <p>${answer.replace(/\n/g, "<br>")}</p>
            </div>
        `;
    } catch (error) {
        console.error("Error fetching answer:", error);
        document.getElementById("recommendations").innerHTML = "<p>Oops! Something glitched. Try again later. 🚨</p>";
    }
}