<?php                                                                 header("Content-Type: application/json");                             header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
                                                                      // Cek Status Server
function getServerStatus() {
    $output = shell_exec('screen -list | grep servermc');                 return $output ? "Online" : "Offline";                            }

// Ambil Log Konsol secara real-time
function getServerLog() {
    $logFile = __DIR__ . "/logs/latest.log";
    if (!file_exists($logFile) || filesize($logFile) === 0) {
        return "Log masih kosong!";
    }
    $logContent = shell_exec("timeout 1 tail -n 50 " . escapeshellarg($logFile));
    return $logContent ?: "";
}

// Ambil data dari request
$method = $_SERVER["REQUEST_METHOD"];                                 $data = json_decode(file_get_contents("php://input"), true);

// Routing API
if ($method === "GET" && isset($_GET["fetch"])) {
    if ($_GET["fetch"] === "status") {
        echo json_encode(["status" => getServerStatus()]);
    } elseif ($_GET["fetch"] === "log") {
        echo json_encode(["log" => nl2br(htmlspecialchars(getServerLog()))]);
    }
    exit;
} elseif ($method === "POST") {
    $action = $data["action"] ?? "";

    if ($action === "start" && getServerStatus() === "Offline") {
        shell_exec("screen -dmS servermc java -Xmx2G -Xms1G -jar server.jar nogui");
        echo json_encode(["message" => "Server dimulai"]);
    } elseif ($action === "stop") {
        shell_exec("screen -S servermc -X stuff 'stop\n'");
        sleep(5);
        shell_exec("screen -X -S servermc quit");
        if (file_exists("world/session.lock")) {
            unlink("world/session.lock");
        }
        echo json_encode(["message" => "Server dihentikan"]);
    } elseif ($action === "restart") {
        shell_exec("screen -S servermc -X stuff 'stop\n'");
        sleep(5);
        shell_exec("screen -X -S servermc quit");
        shell_exec("screen -dmS servermc java -Xmx2G -Xms1G -jar server.jar nogui");
        echo json_encode(["message" => "Server dimulai ulang"]);
    } elseif ($action === "sendCommand" && isset($data["command"])) {
        $command = escapeshellarg($data["command"]);
        shell_exec("screen -S servermc -X stuff '$command\n'");
        echo json_encode(["message" => "Perintah dikirim"]);
    } else {
        echo json_encode(["error" => "Aksi tidak valid"]);
    }
    exit;
}

echo json_encode(["error" => "Metode tidak diizinkan"]);
exit;