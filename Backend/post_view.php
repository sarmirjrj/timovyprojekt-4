<?php
require 'db.php';
require 'auth.php';
include 'header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

/* ===========================
   DETAIL PRÍSPEVKU (id > 0)
   =========================== */
if ($id > 0) {
  // Načítaj príspevok aj s likes/dislikes
  $ps = $mysqli->prepare("SELECT p.post_id, p.title, p.content, p.created_at,
                                 p.likes, p.dislikes,
                                 g.group_id, g.name AS group_name, g.year,
                                 u.user_id, u.username
                          FROM posts p
                          JOIN users u ON u.user_id = p.user_id
                          LEFT JOIN groups g ON g.group_id = p.group_id
                          WHERE p.post_id = ?");
  $ps->bind_param('i', $id);
  $ps->execute();
  $post = $ps->get_result()->fetch_assoc();
  if (!$post) { echo '<p>Post nenájdený.</p>'; include 'footer.php'; exit; }

  // Zoznam príloh
  $fs = $mysqli->prepare("SELECT file_id, file_name, file_path FROM post_files WHERE post_id=?");
  $fs->bind_param('i', $id);
  $fs->execute();
  $files = $fs->get_result();

  // Komentáre
  $cs = $mysqli->prepare("SELECT c.comment_id, c.content, c.created_at, u.username, u.user_id
                          FROM comments c
                          JOIN users u ON u.user_id = c.user_id
                          WHERE c.post_id = ?
                          ORDER BY c.created_at ASC");
  $cs->bind_param('i', $id);
  $cs->execute();
  $comments = $cs->get_result();

  // Ako hlasoval aktuálny používateľ (1, -1, alebo 0 = nehlasoval)
  $likes = (int)$post['likes'];
  $dislikes = (int)$post['dislikes'];
  $user_vote = 0;
  if (is_logged_in()) {
    $uv = $mysqli->prepare("SELECT value FROM post_votes WHERE post_id=? AND user_id=?");
    $uid = current_user_id();
    $uv->bind_param('ii', $id, $uid);
    $uv->execute();
    $uv->bind_result($v);
    if ($uv->fetch()) { $user_vote = (int)$v; }
    $uv->close();
  }
  ?>
  <h2><?= htmlspecialchars($post['title']) ?></h2>
  <p class="text-muted">
    @<?= htmlspecialchars($post['username']) ?>
    <?php if (!empty($post['group_id'])): ?>
      | Skupina: <a href="group_detail.php?id=<?= (int)$post['group_id'] ?>"><?= htmlspecialchars($post['group_name']) ?></a> (<?= (int)$post['year'] ?>)
    <?php endif; ?>
    | <?= htmlspecialchars($post['created_at']) ?>
  </p>

  <!-- Hlasovanie + počty -->
  <div class="my-3 d-flex align-items-center gap-2">
    <?php if (is_logged_in()): ?>
      <form method="post" action="vote.php" class="d-inline">
        <input type="hidden" name="post_id" value="<?= (int)$post['post_id'] ?>">
        <input type="hidden" name="action" value="up">
        <button class="btn <?= ($user_vote===1?'btn-success':'btn-outline-success') ?>">▲ Upvote</button>
      </form>
      <span class="badge bg-success"><?= $likes ?></span>

      <form method="post" action="vote.php" class="d-inline">
        <input type="hidden" name="post_id" value="<?= (int)$post['post_id'] ?>">
        <input type="hidden" name="action" value="down">
        <button class="btn <?= ($user_vote===-1?'btn-danger':'btn-outline-danger') ?>">▼ Downvote</button>
      </form>
      <span class="badge bg-danger"><?= $dislikes ?></span>
    <?php else: ?>
      <span class="text-muted">Na hlasovanie sa prihlás.</span>
    <?php endif; ?>
  </div>

  <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>

  <?php if ($files && $files->num_rows): ?>
    <h5>Prílohy</h5>
    <ul>
      <?php while($f = $files->fetch_assoc()): ?>
        <li><a target="_blank" href="<?= htmlspecialchars($f['file_path']) ?>"><?= htmlspecialchars($f['file_name']) ?></a></li>
      <?php endwhile; ?>
    </ul>
  <?php endif; ?>

  <?php if (is_logged_in() && (current_user_id() == $post['user_id'] || is_admin())): ?>
    <form method="post" action="post_delete.php" onsubmit="return confirm('Zmazať príspevok?');" class="mb-3">
      <input type="hidden" name="post_id" value="<?= (int)$post['post_id'] ?>">
      <button class="btn btn-danger">🗑 Zmazať</button>
    </form>
  <?php endif; ?>

  <hr>
  <h4>Komentáre</h4>
  <?php while($c = $comments->fetch_assoc()): ?>
    <div class="mb-2 p-2 border rounded">
      <div><?= nl2br(htmlspecialchars($c['content'])) ?></div>
      <small class="text-muted">@<?= htmlspecialchars($c['username']) ?> | <?= htmlspecialchars($c['created_at']) ?></small>
    </div>
  <?php endwhile; ?>

  <?php if (is_logged_in()): ?>
    <form method="post" action="comment_store.php" class="mt-3">
      <input type="hidden" name="post_id" value="<?= (int)$post['post_id'] ?>">
      <label class="form-label">Nový komentár</label>
      <textarea class="form-control" name="content" rows="3" required></textarea>
      <button class="btn btn-primary mt-2">Odoslať</button>
    </form>
  <?php else: ?>
    <p class="text-muted">Na komentovanie sa prihlás.</p>
  <?php endif;

  include 'footer.php';
  exit;
}

/* ===========================
   FEED + VYHĽADÁVANIE (bez id)
   =========================== */
$search = trim($_GET['q'] ?? '');
$filter_year  = isset($_GET['year']) ? (int)$_GET['year'] : 0;
$filter_group = isset($_GET['group_id']) ? (int)$_GET['group_id'] : 0;

$sql = "SELECT p.post_id, p.title, LEFT(p.content,160) AS preview, p.created_at,
               p.likes, p.dislikes,
               u.username, g.group_id, g.name AS group_name, g.year
        FROM posts p
        JOIN users u ON u.user_id = p.user_id
        LEFT JOIN groups g ON g.group_id = p.group_id";
$conds = [];
$params = [];
$types  = "";

if ($search !== '') {
  $conds[] = "(p.title LIKE CONCAT('%',?,'%') OR p.content LIKE CONCAT('%',?,'%'))";
  $types  .= "ss";
  $params[] = $search;
  $params[] = $search;
}
if ($filter_year > 0) {
  $conds[] = "g.year = ?";
  $types  .= "i";
  $params[] = $filter_year;
}
if ($filter_group > 0) {
  $conds[] = "p.group_id = ?";
  $types  .= "i";
  $params[] = $filter_group;
}
if (!empty($conds)) {
  $sql .= " WHERE " . implode(" AND ", $conds);
}
$sql .= " ORDER BY p.created_at DESC LIMIT 100";

$stmt = $mysqli->prepare($sql);
if (!$stmt) {
  echo "<pre>Chyba SQL prepare: ".htmlspecialchars($mysqli->error)."</pre>";
  include 'footer.php'; exit;
}

if ($types !== "") {
  $bind = [];
  $bind[] = & $types;
  for ($i=0; $i<count($params); $i++) {
    $bind[] = & $params[$i];
  }
  call_user_func_array([$stmt, 'bind_param'], $bind);
}

$stmt->execute();
$res = $stmt->get_result();

/* zoznam skupín do selectu */
$gl = $mysqli->query("SELECT group_id, name, year FROM groups ORDER BY year, name");
?>
<h2>Feed</h2>
<form class="row g-3 mb-3" method="get" action="post_view.php">
  <div class="col-md-4">
    <input class="form-control" type="text" name="q" placeholder="Hľadať v názve/obsahu" value="<?= htmlspecialchars($search) ?>">
  </div>
  <div class="col-md-2">
    <input class="form-control" type="number" name="year" placeholder="Ročník" value="<?= $filter_year ?: '' ?>">
  </div>
  <div class="col-md-4">
    <select class="form-select" name="group_id">
      <option value="">-- filtrovať podľa skupiny --</option>
      <?php if ($gl): while($g = $gl->fetch_assoc()): ?>
        <option value="<?= (int)$g['group_id'] ?>" <?= ($filter_group == $g['group_id']) ? 'selected' : '' ?>>
          <?= htmlspecialchars($g['name']) ?> (<?= (int)$g['year'] ?>)
        </option>
      <?php endwhile; endif; ?>
    </select>
  </div>
  <div class="col-md-2">
    <button class="btn btn-secondary w-100">Filtrovať</button>
  </div>
</form>

<div class="list-group">
<?php while($r = $res->fetch_assoc()): ?>
  <a class="list-group-item list-group-item-action" href="post_view.php?id=<?= (int)$r['post_id'] ?>">
    <div class="d-flex w-100 justify-content-between">
      <h5 class="mb-1"><?= htmlspecialchars($r['title']) ?></h5>
      <small><?= htmlspecialchars($r['created_at']) ?></small>
    </div>
    <p class="mb-1"><?= htmlspecialchars($r['preview']) ?>…</p>
    <small class="text-muted">
      @<?= htmlspecialchars($r['username']) ?> |
      <?= htmlspecialchars($r['group_name'] ?? 'Bez skupiny') ?>
      <?php if (!empty($r['year'])): ?>(<?= (int)$r['year'] ?>)<?php endif; ?> |
      ▲ <?= (int)$r['likes'] ?> · ▼ <?= (int)$r['dislikes'] ?>
    </small>
  </a>
<?php endwhile; ?>
</div>

<?php include 'footer.php'; ?>
