<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Server Minecraft</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css" rel="stylesheet">
    <style>
        .console {
            max-height: 400px;
            overflow-y: auto;
            background-color: black;
            color: lime;
            font-family: monospace;
            padding: 10px;
            border-radius: 5px;
        }
    </style>
</head>
<body class="bg-dark text-light">
    <div class="container py-5">
        <h1 class="text-center mb-4">Panel Server Minecraft</h1>

        <div id="serverStatus" class="status mb-4 p-3 rounded-3 text-center text-white bg-secondary">
            Status: Loading...
        </div>

        <div class="text-center mb-4">
            <button id="startButton" class="btn btn-success"><i class="fas fa-play"></i> Start</button>
            <button id="stopButton" class="btn btn-danger d-none"><i class="fas fa-stop"></i> Stop</button>
            <button id="restartButton" class="btn btn-warning"><i class="fas fa-sync-alt"></i> Restart</button>
            <a href="/manager.php" class="btn btn-primary"><i class="fas fa-cogs"></i> Manager</a>
        </div>

        <h2 class="text-center text-white mb-4">Console Log</h2>
        <div class="console" id="console">
            Loading...
        </div>

        <div class="text-center mb-4">
            <input type="text" id="commandInput" class="form-control w-50 d-inline-block" placeholder="Command">
            <button id="sendCommandButton" class="btn btn-info"><i class="fas fa-terminal"></i> Enter</button>
        </div>
    </div>

    <script>
        const API_URL = "api.php";

        function fetchData(type, callback) {
            fetch(API_URL + "?fetch=" + type)
            .then(response => response.json())
            .then(data => callback(data))
            .catch(error => console.error("Fetch error:", error));
        }

        function updateStatus() {
            fetchData("status", data => {
                let statusDiv = document.getElementById("serverStatus");
                let startButton = document.getElementById("startButton");
                let stopButton = document.getElementById("stopButton");

                statusDiv.innerHTML = "Status: " + data.status;
                statusDiv.className = "status mb-4 p-3 rounded-3 text-center text-white " +
                (data.status === "Online" ? "bg-success" : "bg-danger");

                if (data.status === "Online") {
                    startButton.classList.add("d-none");
                    stopButton.classList.remove("d-none");
                } else {
                    startButton.classList.remove("d-none");
                    stopButton.classList.add("d-none");
                }
            });
        }

        function updateConsole() {
            fetchData("log", data => {
                let consoleDiv = document.getElementById("console");
                consoleDiv.innerHTML = data.log;
                consoleDiv.scrollTop = consoleDiv.scrollHeight;
            });
        }

        function sendAction(action, command = null) {
            let payload = { action };
            if (command) payload.command = command;

            fetch(API_URL, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(payload)
            })
            .then(response => response.json())
            .then(data => alert(data.message || data.error))
            .catch(error => console.error("Error:", error));
        }

        document.getElementById("startButton").addEventListener("click", () => sendAction("start"));
        document.getElementById("stopButton").addEventListener("click", () => sendAction("stop"));
        document.getElementById("restartButton").addEventListener("click", () => sendAction("restart"));
        document.getElementById("sendCommandButton").addEventListener("click", () => {
            let command = document.getElementById("commandInput").value;
            if (command) sendAction("sendCommand", command);
        });

        setInterval(updateStatus, 2000);
        setInterval(updateConsole, 2000);
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
