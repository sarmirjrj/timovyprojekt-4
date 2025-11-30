<?php
require 'db.php';
require 'auth.php';
include 'header.php';

/* --- vstupy / filtre --- */
$q         = trim($_GET['q'] ?? '');
$group_id  = isset($_GET['group_id']) ? (int)$_GET['group_id'] : 0;
$year      = isset($_GET['year']) ? (int)$_GET['year'] : 0;
$filetype  = trim($_GET['type'] ?? ''); // pdf / jpg / png / ...
$order     = ($_GET['order'] ?? 'recent') === 'name' ? 'name' : 'recent';

$page      = max(1, (int)($_GET['p'] ?? 1));
$pageSize  = 50;
$offset    = ($page - 1) * $pageSize;

$baseSql =
" FROM post_files pf
  JOIN posts p   ON p.post_id  = pf.post_id
  JOIN users u   ON u.user_id  = p.user_id
  LEFT JOIN groups g ON g.group_id = p.group_id
  WHERE 1=1";

$conds = [];
$params = [];
$types  = "";

/* vyhladavanie podla nazvu suboru alebo nazvu prispevku */
if ($q !== '') {
  $conds[] = "(pf.file_name LIKE CONCAT('%',?,'%') OR p.title LIKE CONCAT('%',?,'%'))";
  $types  .= "ss";
  $params[] = $q;
  $params[] = $q;
}

/* filter skupina */
if ($group_id > 0) {
  $conds[] = "p.group_id = ?";
  $types  .= "i";
  $params[] = $group_id;
}

/* filter rocnik */
if ($year > 0) {
  $conds[] = "g.year = ?";
  $types  .= "i";
  $params[] = $year;
}

/* filter typu (pripony) */
if ($filetype !== '') {
  $conds[] = "pf.file_type = ?";
  $types  .= "s";
  $params[] = $filetype;
}

$where = "";
if (!empty($conds)) {
  $where = " AND " . implode(" AND ", $conds);
}


$countSql = "SELECT COUNT(*)" . $baseSql . $where;
$stc = $mysqli->prepare($countSql);
if ($types !== "") {
  $bind1 = [];
  $bind1[] = &$types;
  for ($i=0;$i<count($params);$i++) $bind1[] = &$params[$i];
  call_user_func_array([$stc, 'bind_param'], $bind1);
}
$stc->execute();
$stc->bind_result($totalRows);
$stc->fetch();
$stc->close();

$totalPages = max(1, ceil($totalRows / $pageSize));

/* zoradenie */
$orderSql = ($order === 'name')
  ? " ORDER BY pf.file_name ASC"
  : " ORDER BY COALESCE(pf.uploaded_at, p.created_at) DESC";

$selectSql =
"SELECT pf.file_id, pf.file_name, pf.file_path, pf.file_type,
        COALESCE(pf.uploaded_at, p.created_at) AS added_at,
        p.post_id, p.title AS post_title,
        u.username,
        g.group_id, g.name AS group_name, g.year" .
$baseSql . $where . $orderSql . " LIMIT ? OFFSET ?";

$stmt = $mysqli->prepare($selectSql);
if (!$stmt) {
  echo "<pre>Chyba SQL prepare: ".htmlspecialchars($mysqli->error)."</pre>";
  include 'footer.php'; exit;
}

$types2  = $types . "ii";
$params2 = $params;
$params2[] = $pageSize;
$params2[] = $offset;

$bind2 = [];
$bind2[] = &$types2;
for ($i=0; $i<count($params2); $i++) $bind2[] = &$params2[$i];
call_user_func_array([$stmt, 'bind_param'], $bind2);

$stmt->execute();
$files = $stmt->get_result();

$groups = $mysqli->query("SELECT group_id, name, year FROM groups ORDER BY year, name");

$typesList = $mysqli->query("SELECT DISTINCT file_type FROM post_files WHERE file_type IS NOT NULL AND file_type<>'' ORDER BY file_type");

?>
<h2>Knižnica</h2>

<form class="row g-3 mb-3" method="get" action="library.php">
  <div class="col-md-4">
    <input class="form-control" type="text" name="q" placeholder="Hľadať názov súboru alebo príspevku" value="<?= htmlspecialchars($q) ?>">
  </div>
  <div class="col-md-3">
    <select class="form-select" name="group_id">
      <option value="">-- filtrovať podľa skupiny --</option>
      <?php if ($groups): while($g = $groups->fetch_assoc()): ?>
        <option value="<?= (int)$g['group_id'] ?>" <?= ($group_id == $g['group_id']) ? 'selected' : '' ?>>
          <?= htmlspecialchars($g['name']) ?> (<?= (int)$g['year'] ?>)
        </option>
      <?php endwhile; endif; ?>
    </select>
  </div>
  <div class="col-md-2">
    <input class="form-control" type="number" name="year" placeholder="Ročník" value="<?= $year ?: '' ?>">
  </div>
  <div class="col-md-2">
    <select class="form-select" name="type">
      <option value="">-- typ súboru --</option>
      <?php if ($typesList): while($t = $typesList->fetch_assoc()): ?>
        <option value="<?= htmlspecialchars($t['file_type']) ?>" <?= ($filetype===$t['file_type']) ? 'selected' : '' ?>>
          <?= strtoupper(htmlspecialchars($t['file_type'])) ?>
        </option>
      <?php endwhile; endif; ?>
    </select>
  </div>
  <div class="col-md-1">
    <select class="form-select" name="order" title="Zoradenie">
      <option value="recent" <?= ($order==='recent'?'selected':'') ?>>Nové</option>
      <option value="name"   <?= ($order==='name'  ?'selected':'') ?>>Názov</option>
    </select>
  </div>
  <div class="col-12">
    <button class="btn btn-secondary">Filtrovať</button>
  </div>
</form>

<?php if ($totalRows == 0): ?>
  <div class="alert alert-info">Zatiaľ tu nie sú žiadne súbory.</div>
<?php else: ?>
  <div class="list-group mb-3">
    <?php while($f = $files->fetch_assoc()): ?>
      <a class="list-group-item list-group-item-action" href="<?= htmlspecialchars($f['file_path']) ?>" target="_blank">
        <div class="d-flex w-100 justify-content-between">
          <h5 class="mb-1"><?= htmlspecialchars($f['file_name']) ?></h5>
          <small><?= htmlspecialchars($f['added_at']) ?></small>
        </div>
        <p class="mb-1">
          Príspevok: <?= htmlspecialchars($f['post_title']) ?> |
          Autor: @<?= htmlspecialchars($f['username']) ?>
        </p>
        <small class="text-muted">
          <?php if (!empty($f['group_id'])): ?>
            <span class="badge bg-primary">Skupina: <?= htmlspecialchars($f['group_name']) ?> (<?= (int)$f['year'] ?>)</span>
          <?php else: ?>
            <span class="badge bg-secondary">Bez skupiny</span>
          <?php endif; ?>
          <?php if (!empty($f['file_type'])): ?>
            <span class="badge bg-dark ms-2"><?= strtoupper(htmlspecialchars($f['file_type'])) ?></span>
          <?php endif; ?>
        </small>
      </a>
    <?php endwhile; ?>
  </div>

  <!-- Paginácia -->
  <nav aria-label="Page navigation">
    <ul class="pagination">
      <?php
      // zachovanie filtrov v URL
      $keep = $_GET; unset($keep['p']);
      $base = 'library.php?' . http_build_query($keep);
      if ($page > 1): ?>
        <li class="page-item"><a class="page-link" href="<?= $base ?>&p=<?= $page-1 ?>">&laquo; Predchádzajúca</a></li>
      <?php else: ?>
        <li class="page-item disabled"><span class="page-link">&laquo; Predchádzajúca</span></li>
      <?php endif; ?>

      <li class="page-item disabled"><span class="page-link">Strana <?= $page ?> / <?= $totalPages ?></span></li>

      <?php if ($page < $totalPages): ?>
        <li class="page-item"><a class="page-link" href="<?= $base ?>&p=<?= $page+1 ?>">Ďalšia &raquo;</a></li>
      <?php else: ?>
        <li class="page-item disabled"><span class="page-link">Ďalšia &raquo;</span></li>
      <?php endif; ?>
    </ul>
  </nav>
<?php endif; ?>

<?php include 'footer.php'; ?>
