<?php
session_start();
$rootDir = __DIR__ . '/';
$currentDir = realpath($rootDir . '/' . ($_GET['dir'] ?? '')) ?: $rootDir;

if (!is_dir($rootDir)) {
    mkdir($rootDir, 0777, true);
}

if (strpos($currentDir, realpath($rootDir)) !== 0) {
    $currentDir = $rootDir;
}

function getItems($dir) {
    if (!is_dir($dir)) return [];
    $items = scandir($dir);
    return array_filter($items, fn($item) => $item !== '.' && $item !== '..');                                                              }
                                                                      if (isset($_GET['delete'])) {
    $target = realpath($currentDir . '/' . $_GET['delete']);              if ($target && strpos($target, realpath($rootDir)) === 0) {
        is_dir($target) ? rmdir($target) : unlink($target);               }
    header("Location: ?dir=" . urlencode($_GET['dir'] ?? ''));            exit;
}

if (isset($_POST['new_folder'])) {
    mkdir($currentDir . '/' . $_POST['new_folder']);
    header("Location: ?dir=" . urlencode($_GET['dir'] ?? ''));
    exit;
}
                                                                      if (isset($_POST['new_file'])) {
    file_put_contents($currentDir . '/' . $_POST['new_file'], '');
    header("Location: ?dir=" . urlencode($_GET['dir'] ?? ''));
    exit;
}

if (!empty($_FILES['file'])) {
    move_uploaded_file($_FILES['file']['tmp_name'], $currentDir . '/' . $_FILES['file']['name']);
    header("Location: ?dir=" . urlencode($_GET['dir'] ?? ''));
    exit;
}

if (isset($_POST['edit_file'])) {
    file_put_contents($currentDir . '/' . $_POST['edit_file'], $_POST['content']);
    header("Location: ?dir=" . urlencode($_GET['dir'] ?? ''));
    exit;
}

$relativeDir = str_replace(realpath($rootDir), '', $currentDir);
$parentDir = dirname($relativeDir);
$backLink = $relativeDir !== '' ? "<a href='?dir=$parentDir' class='btn btn-secondary'><i class='bi bi-arrow-left'></i> Kembali</a>" : "";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="container py-4 bg-dark text-light">
    <h2 class="mb-3"><i class="bi bi-folder"></i> File Manager</h2>
    <p>Folder saat ini: <?= $currentDir ?></p>
    <div class="d-flex gap-2 mb-3">
        <?= $backLink ?>
        <a href="/" class="btn btn-primary"><i class="bi bi-terminal"></i> Console</a>
    </div>
    <div class="d-flex gap-2 mb-3">
        <form method="post" enctype="multipart/form-data" class="flex-grow-1">
            <input type="file" name="file" class="form-control bg-dark text-light mb-2">
            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-upload"></i> Upload</button>
        </form>
        <form method="post" class="flex-grow-1">
            <input type="text" name="new_folder" class="form-control bg-dark text-light mb-2" placeholder="Nama Folder Baru">
            <button type="submit" class="btn btn-success w-100"><i class="bi bi-folder-plus"></i> Buat Folder</button>
        </form>
        <form method="post" class="flex-grow-1">
            <input type="text" name="new_file" class="form-control bg-dark text-light mb-2" placeholder="Nama File Baru">
            <button type="submit" class="btn btn-info w-100"><i class="bi bi-file-earmark-plus"></i> Buat File</button>
        </form>
    </div>
    <ul class="list-group">
        <?php foreach (getItems($currentDir) as $item):
            $path = "$currentDir/$item";
            $encodedPath = urlencode(($_GET['dir'] ?? '') . '/' . $item);
        ?>
            <li class="list-group-item d-flex justify-content-between align-items-center bg-dark text-light">
                <?php if (is_dir($path)): ?>
                    <a href="?dir=<?= $encodedPath ?>" class="text-light"><i class="bi bi-folder"></i> <?= $item ?></a>
                <?php else: ?>
                    <span><i class="bi bi-file-earmark"></i> <?= $item ?></span>
                <?php endif; ?>
                <div>
                    <?php if (!is_dir($path)): ?>
                        <a href="?edit=<?= $encodedPath ?>" class="btn btn-warning btn-sm"><i class="bi bi-pencil"></i> Edit</a>
                    <?php endif; ?>
                    <a href="?delete=<?= $encodedPath ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus file?')"><i class="bi bi-trash"></i> Hapus</a>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
    <?php if (isset($_GET['edit'])):
        $editFile = realpath($currentDir . '/' . $_GET['edit']);
        if ($editFile && strpos($editFile, realpath($rootDir)) === 0 && is_file($editFile)):
            $content = htmlspecialchars(file_get_contents($editFile));
    ?>
        <form method="post" class="mt-4">
            <textarea name="content" class="form-control bg-dark text-light" rows="10"><?= $content ?></textarea>
            <input type="hidden" name="edit_file" value="<?= $_GET['edit'] ?>">
            <button type="submit" class="btn btn-primary mt-2"><i class="bi bi-save"></i> Simpan</button>
        </form>
    <?php endif; endif; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>