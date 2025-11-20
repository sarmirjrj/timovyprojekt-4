<?php
include 'header.php';
require 'auth.php';
require_login();
require 'db.php';

$q = $mysqli->query("SELECT * FROM file_library ORDER BY uploaded_at DESC");
$files = $q->fetch_all(MYSQLI_ASSOC);
?>

<div class="container mt-5">

    <h1 class="mb-4">Knižnica súborov</h1>

    <?php if (empty($files)): ?>
        <div class="alert alert-info">Zatiaľ tu nie sú žiadne nahraté súbory.</div>
    <?php else: ?>

    <div class="table-responsive">
        <table class="table table-dark table-striped align-middle">
            <thead>
            <tr>
                <th>Názov</th>
                <th>Typ</th>
                <th>Skupina</th>
                <th>Príspevok</th>
                <th>Autor</th>
                <th>Dátum</th>
                <th></th>
            </tr>
            </thead>

            <tbody>
            <?php foreach ($files as $f): ?>
                <tr>
                    <td><?= htmlspecialchars($f['file_name']) ?></td>
                    <td><?= htmlspecialchars(strtoupper($f['file_type'])) ?></td>

                    <td>
                        <?php if ($f['group_id']): ?>
                            <a href="group_detail.php?id=<?= $f['group_id'] ?>" class="text-info">
                                <?= htmlspecialchars($f['group_name']) ?>
                            </a>
                        <?php else: ?>
                            <span class="text-secondary">–</span>
                        <?php endif; ?>
                    </td>

                    <td>
                        <?php if ($f['post_id']): ?>
                            <a href="post_view.php?id=<?= $f['post_id'] ?>" class="text-warning">
                                <?= htmlspecialchars($f['post_title']) ?>
                            </a>
                        <?php else: ?>
                            <span class="text-secondary">–</span>
                        <?php endif; ?>
                    </td>

                    <td><?= htmlspecialchars($f['uploaded_by']) ?></td>
                    <td><?= htmlspecialchars($f['uploaded_at']) ?></td>

                </tr>
            <?php endforeach; ?>
            </tbody>

        </table>
    </div>

    <?php endif; ?>

</div>
