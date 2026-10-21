<?php
declare(strict_types=1);
require __DIR__ . '/lib_belajar.php';
start_session();
$m = preg_match('/^[a-z][0-9]{1,2}$/', (string)($_GET['m'] ?? '')) ? (string)$_GET['m'] : '';
$u = sv_require_user('/belajar/' . ($m ? '?m=' . $m : ''));
$q = bl_db()->prepare('SELECT modul, skor, total, terbaik FROM belajar WHERE user_id = ?');
$q->execute([$u['id']]);
$prog = [];
foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $r) $prog[$r['modul']] = ['skor' => (int)$r['skor'], 'total' => (int)$r['total'], 'terbaik' => (int)$r['terbaik']];
sv_head('Ruang Belajar', $m ? '<a href="/belajar/">Ruang Belajar</a>' : 'Ruang Belajar');
?>
<style>
.rooms{display:grid;gap:18px;grid-template-columns:repeat(auto-fit,minmax(320px,1fr))}
.room{overflow:hidden;display:flex;flex-direction:column}
.room .rh{display:flex;gap:16px;align-items:center;padding:22px 22px 18px;background:var(--deep);color:#FFFFF0}
.room.riset .rh{background:#5C4A12}
.room .rh img{width:76px;height:76px;border-radius:50%;object-fit:cover;border:3px solid rgba(255,255,240,.6);flex:none;background:var(--mist)}
.room .rh h2{font-size:22px;color:#FFFFF0}
.room .rh p{margin:4px 0 0;font-size:14px;opacity:.88}
.room .bar{height:6px;background:rgba(255,255,240,.25);border-radius:9px;margin-top:10px;overflow:hidden}.room .bar i{display:block;height:100%;background:var(--gold);border-radius:9px}
.mods{list-style:none;margin:0;padding:8px 10px 14px;display:flex;flex-direction:column}
.mods li a,.mods li .x{display:flex;gap:14px;align-items:flex-start;padding:14px 12px;border-radius:14px;text-decoration:none;color:var(--ink)}
.mods li a:hover{background:var(--mist)}
.mods .no{width:34px;height:34px;border-radius:50%;display:grid;place-items:center;font-weight:800;font-size:14px;background:var(--mist);color:var(--deep);flex:none}
.mods .done .no{background:var(--deep);color:#FFFFF0}
.mods b{display:block;font-size:15.5px}
.mods small{display:block;color:var(--mute);font-size:13px;margin-top:3px;font-weight:500}
.mods .tag{margin-left:auto;align-self:center;white-space:nowrap}
.mods .soon{opacity:.72}
.tag{font-size:11.5px;font-weight:800;border-radius:999px;padding:3px 10px}
.tag.s{background:var(--mist);color:var(--mute)}.tag.r{background:#DDF0E1;color:var(--deep)}.tag.d{background:var(--gold-soft);color:#7A5410}
/* modul */
.mv{max-width:760px;margin:0 auto;display:flex;flex-direction:column;gap:16px}
.mvh{display:flex;gap:16px;align-items:center}
.mvh img{width:64px;height:64px;border-radius:50%;object-fit:cover;flex:none}
.goal{padding:18px 22px}.goal ul{margin:8px 0 0;padding-left:20px;display:flex;flex-direction:column;gap:4px}
.sec{padding:22px clamp(18px,4vw,30px);display:flex;flex-direction:column;gap:10px}
.sec h2{font-size:19px}.sec p{margin:0;line-height:1.7}
.sec .n{font-size:12px;font-weight:800;color:var(--gold);letter-spacing:.1em;text-transform:uppercase}
.rumus{background:var(--deep);color:#FFFFF0;border-radius:14px;padding:12px 16px;font-weight:700;font-size:14.5px;white-space:pre-line}
.contoh{background:var(--bg);border:1px solid var(--line);border-left:4px solid var(--gold);border-radius:12px;padding:12px 16px;font-size:14.5px;white-space:pre-line}
.contoh b{display:block;font-size:11.5px;letter-spacing:.08em;text-transform:uppercase;color:#7A5410;margin-bottom:4px}
.sec ul{margin:0;padding-left:20px;display:flex;flex-direction:column;gap:6px;line-height:1.6}
.quiz{padding:22px clamp(18px,4vw,30px);display:flex;flex-direction:column;gap:18px}
.qq{display:flex;flex-direction:column;gap:8px}.qq .qt{font-weight:700}
.qo{display:flex;align-items:center;gap:10px;border:1.5px solid var(--line);border-radius:12px;padding:10px 14px;cursor:pointer;min-height:46px}
.qo input{width:18px;height:18px;accent-color:var(--deep);margin:0}
.qo.ok{border-color:#2E7D4F;background:#EEF6EF}.qo.no{border-color:#B4532A;background:var(--warn-soft)}
.qe{font-size:13.5px;color:var(--mute);border-left:3px solid var(--line);padding-left:10px}
.prak{padding:20px 22px;background:var(--deep);color:#FFFFF0;display:flex;gap:12px;align-items:center;flex-wrap:wrap;border-radius:var(--r)}
.prak p{margin:0;flex:1;min-width:200px;font-weight:700}
.prak .btn{background:var(--gold);color:var(--ink)}
.nav2{display:flex;gap:10px;flex-wrap:wrap;justify-content:space-between}
</style>
<main class="wrap" id="app" style="padding-top:22px"></main>
<script>var CSRF=<?= json_encode($_SESSION['csrf']) ?>,PROG=<?= json_encode((object)$prog) ?>,MOD=<?= json_encode($m) ?>,NAMA=<?= json_encode($u['name'], JSON_HEX_TAG) ?>;</script>
<script src="/belajar/modul.js?v=1"></script>
<script src="/belajar/belajar.js?v=1"></script>
<script src="/js/stat.js" defer></script>
</body></html>
