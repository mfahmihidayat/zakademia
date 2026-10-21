<?php
declare(strict_types=1);
require __DIR__ . '/lib_analitik.php';
start_session();
$u = sv_require_user('/analitik/');
$q = an_db()->prepare('SELECT id, label, tahun, data, updated FROM analitik WHERE user_id = ? ORDER BY tahun DESC, updated DESC');
$q->execute([$u['id']]);
$saved = array_map(fn($r) => ['id' => (int)$r['id'], 'label' => $r['label'], 'tahun' => (int)$r['tahun'], 'data' => json_decode($r['data'], true)], $q->fetchAll(PDO::FETCH_ASSOC));
sv_head('Kesehatan Penghimpunan', 'Data &amp; Analitik');
?>
<style>
.grid2{display:grid;gap:18px;grid-template-columns:minmax(0,1fr)}
@media (min-width:1020px){.grid2{grid-template-columns:420px minmax(0,1fr);align-items:start}.inbox{position:sticky;top:76px;max-height:calc(100vh - 92px);overflow:auto}}
.inbox{padding:20px;display:flex;flex-direction:column;gap:14px}
.fld{display:flex;flex-direction:column;gap:6px}
.fld .lab{font-weight:700;font-size:14px}
.fld .hint{font-size:12.5px;color:var(--mute);margin-top:-3px}
.pair{display:grid;grid-template-columns:1fr 1fr;gap:8px}
.pair span{font-size:11.5px;color:var(--mute);font-weight:700;letter-spacing:.04em;text-transform:uppercase;display:block;margin-bottom:3px}
.num{position:relative}.num i{position:absolute;left:12px;top:50%;transform:translateY(-50%);font-style:normal;color:var(--mute);font-size:14px;pointer-events:none}
.num input{padding-left:36px;font-variant-numeric:tabular-nums}
.num.cnt input{padding-left:13px}
.grp{font-size:12px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--deep);border-top:1px solid var(--line);padding-top:12px}
.res{display:flex;flex-direction:column;gap:14px}
.kp{display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:12px}
.m{padding:16px 18px;display:flex;flex-direction:column;gap:6px;border-top:5px solid var(--line)}
.m.ok{border-top-color:#2E7D4F}.m.warn{border-top-color:var(--gold)}.m.bad{border-top-color:#B4532A}
.m .t{font-size:12.5px;font-weight:700;color:var(--mute);text-transform:uppercase;letter-spacing:.06em}
.m .v{font-size:28px;font-weight:800;letter-spacing:-.02em;line-height:1.15}
.m .st{font-size:12px;font-weight:800;border-radius:999px;padding:2px 9px;align-self:flex-start}
.m.ok .st{background:#DDF0E1;color:#1F5E3A}.m.warn .st{background:var(--gold-soft);color:#7A5410}.m.bad .st{background:var(--warn-soft);color:var(--warn)}.m.na .st{background:var(--mist);color:var(--mute)}
.m p{margin:0;font-size:13.5px;color:var(--mute)}
.sec{padding:20px;display:flex;flex-direction:column;gap:12px}
.flow{display:flex;flex-direction:column;gap:10px}
.flow .r{display:grid;grid-template-columns:96px 1fr;gap:10px;align-items:center;font-size:13.5px;font-weight:600}
.flow .b{display:flex;height:34px;border-radius:9px;overflow:hidden;background:var(--mist)}
.flow .b i{display:flex;align-items:center;justify-content:center;font-style:normal;font-size:12.5px;font-weight:700;color:#fff;white-space:nowrap;overflow:hidden;min-width:0}
.c-tahan{background:#0B4D33}.c-hilang{background:#B4532A}.c-baru{background:#6FAF78}.c-kembali{background:#C99A3B}
.legend{display:flex;gap:6px 16px;flex-wrap:wrap;font-size:12.5px;color:var(--mute)}.legend span{display:inline-flex;align-items:center;gap:6px}.legend em{width:11px;height:11px;border-radius:3px;display:inline-block}
.sl{display:grid;grid-template-columns:minmax(150px,1fr) 2fr 86px;gap:10px;align-items:center;font-size:14px}
.sl input{accent-color:var(--deep);width:100%}
.sl b{text-align:right;font-variant-numeric:tabular-nums}
.big{font-size:30px;font-weight:800;color:var(--deep);letter-spacing:-.02em}
.reco{margin:0;padding-left:20px;display:flex;flex-direction:column;gap:8px}
.warnbox{background:var(--gold-soft);border:1px solid #EBD6A8;color:#5E430D;border-radius:12px;padding:10px 14px;font-size:13.5px}
.chips{display:flex;gap:8px;flex-wrap:wrap}
.chip{display:inline-flex;align-items:center;gap:2px;border:1px solid var(--line);border-radius:999px;background:var(--paper);padding:2px 4px 2px 12px;font-size:13.5px;font-weight:600}
.chip.on{border-color:var(--deep);background:#EEF6EF}
.chip button{border:0;background:none;cursor:pointer;padding:6px;font:inherit;color:inherit}
.chip button.x{color:var(--mute);font-size:12px}
@media (max-width:560px){.sl{grid-template-columns:1fr 64px}.sl label{grid-column:1/-1}}
.katg{display:grid;grid-template-columns:repeat(auto-fill,minmax(270px,1fr));gap:14px}
.kat{display:flex;flex-direction:column;overflow:hidden}
.kat .viz{position:relative;background:var(--bg);border-bottom:1px solid var(--line);padding:32px 16px 10px}
.kat .ilu{position:absolute;top:8px;right:10px;font-size:10.5px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--mute);background:var(--paper);border:1px solid var(--line);border-radius:999px;padding:1px 8px}
.kat .vz2{display:flex;gap:16px;flex-wrap:wrap;align-items:center}.kat .vz2>div{flex:1 1 230px;min-width:0}
.kat.wide{grid-column:1/-1}
@media (min-width:760px){.kat.wide{flex-direction:row}.kat.wide .viz{flex:1.4;border-bottom:0;border-right:1px solid var(--line);display:flex;align-items:center}.kat.wide .vz2{width:100%}.kat.wide .kb{flex:1;justify-content:center}}
.katcta{background:var(--deep);color:#FFFFF0;border-color:var(--deep)}.katcta .q{opacity:.85}.katcta .btn{background:var(--gold);color:var(--ink)}
.kat .kb{padding:14px 18px 18px;display:flex;flex-direction:column;gap:8px;flex:1}
.kat h3{margin:0;font-size:16px}
.kat .q{margin:0;font-size:14px}
.kat dl{margin:0;display:flex;flex-direction:column;gap:2px;font-size:13px;flex:1}
.kat dt{font-weight:700;color:var(--mute);font-size:11.5px;letter-spacing:.05em;text-transform:uppercase;margin-top:6px}
.kat dd{margin:0;color:var(--mute)}
.kat .btn{align-self:flex-start;margin-top:6px}
@media print{.top,.inbox,.noprint{display:none!important}.grid2{display:block}body{background:#fff}.card{box-shadow:none}}
</style>
<main class="wrap" style="display:flex;flex-direction:column;gap:18px;padding-top:22px">
  <div style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap">
    <div style="flex:1;min-width:240px"><p class="mute small" style="margin:0 0 4px;font-weight:700;letter-spacing:.08em;text-transform:uppercase">Data &amp; Analitik</p>
      <h1>Kesehatan Penghimpunan</h1>
      <p class="mute" style="margin:6px 0 0;max-width:70ch">Isi angka rekap tahunan lembaga. Hasil langsung muncul tanpa perlu mengunggah data donatur: hanya angka total yang diperlukan, tidak ada nama atau data pribadi.</p>
      <p class="mute small" style="margin:8px 0 0;max-width:72ch">Berbeda dengan <a href="/asesmen/">Asesmen Kesiapan Penghimpunan</a>, yang hasilnya ditelaah tim Zakademia lalu dikirim lewat email, hasil di halaman ini langsung keluar dan dapat dipakai saat itu juga.</p></div>
    <div class="noprint" style="display:flex;gap:8px;flex-wrap:wrap"><button class="btn ghost sm" type="button" id="btnNew">Analisis baru</button><button class="btn ghost sm" type="button" onclick="window.print()">Cetak / PDF</button></div>
  </div>
  <div class="chips noprint" id="saved"></div>

  <div class="grid2">
    <form class="card inbox" id="f" autocomplete="off" novalidate>
      <div class="pair">
        <label class="fld"><span class="lab">Tahun dianalisis</span><select class="f" id="tahun"></select></label>
        <div class="fld"><span class="lab">Pembanding</span><input class="f" id="tahunL" disabled></div>
      </div>
      <p class="mute small" style="margin:0">Boleh angka perkiraan. Kosongkan bila tidak tersedia; hasil yang memerlukan angka itu tidak ditampilkan.</p>

      <div class="grp">Penghimpunan</div>
      <div class="fld"><span class="lab">Total dana terhimpun</span><span class="hint">Seluruh jenis dana (zakat, infak, wakaf, dan lainnya) dalam satu tahun.</span>
        <div class="pair"><div><span id="lbL1">Tahun lalu</span><div class="num"><i>Rp</i><input class="f" data-k="danaL" inputmode="numeric" aria-label="Total dana tahun lalu"></div></div><div><span id="lbN1">Tahun ini</span><div class="num"><i>Rp</i><input class="f" data-k="danaN" inputmode="numeric" aria-label="Total dana tahun ini"></div></div></div></div>
      <div class="fld"><span class="lab">Jumlah donatur yang memberi</span><span class="hint">Orang atau lembaga berbeda yang berdonasi minimal sekali dalam tahun itu.</span>
        <div class="pair"><div><span>Tahun lalu</span><div class="num cnt"><input class="f" data-k="donL" inputmode="numeric" aria-label="Jumlah donatur tahun lalu"></div></div><div><span>Tahun ini</span><div class="num cnt"><input class="f" data-k="donN" inputmode="numeric" aria-label="Jumlah donatur tahun ini"></div></div></div></div>
      <div class="fld"><span class="lab">Jumlah transaksi donasi</span><span class="hint">Banyaknya kali donasi diterima.</span>
        <div class="pair"><div><span>Tahun lalu</span><div class="num cnt"><input class="f" data-k="trxL" inputmode="numeric" aria-label="Jumlah transaksi tahun lalu"></div></div><div><span>Tahun ini</span><div class="num cnt"><input class="f" data-k="trxN" inputmode="numeric" aria-label="Jumlah transaksi tahun ini"></div></div></div></div>

      <div class="grp">Donatur tahun ini</div>
      <label class="fld"><span class="lab">Donatur baru</span><span class="hint">Belum pernah berdonasi sebelum tahun ini.</span><div class="num cnt"><input class="f" data-k="baru" inputmode="numeric"></div></label>
      <label class="fld"><span class="lab">Donatur tahun lalu yang memberi lagi tahun ini</span><span class="hint">Dari donatur tahun lalu, berapa yang berdonasi lagi tahun ini.</span><div class="num cnt"><input class="f" data-k="tahan" inputmode="numeric"></div></label>

      <div class="grp">Biaya</div>
      <div class="fld"><span class="lab">Biaya penghimpunan total</span><span class="hint">Gaji tim penghimpunan, kampanye, iklan, materi, biaya kanal pembayaran.</span>
        <div class="pair"><div><span>Tahun lalu</span><div class="num"><i>Rp</i><input class="f" data-k="biayaL" inputmode="numeric" aria-label="Biaya penghimpunan tahun lalu"></div></div><div><span>Tahun ini</span><div class="num"><i>Rp</i><input class="f" data-k="biayaN" inputmode="numeric" aria-label="Biaya penghimpunan tahun ini"></div></div></div></div>
      <label class="fld"><span class="lab">Biaya untuk mendapatkan donatur baru</span><span class="hint">Bagian dari biaya di atas yang dipakai menjaring donatur baru: iklan, kampanye akuisisi, acara. Perkiraan pun boleh.</span><div class="num"><i>Rp</i><input class="f" data-k="akuisisi" inputmode="numeric"></div></label>

      <div class="grp">Opsional</div>
      <label class="fld"><span class="lab">Dana dari 10 donatur terbesar</span><span class="hint">Jumlah donasi tahun ini dari 10 donatur dengan nilai terbesar.</span><div class="num"><i>Rp</i><input class="f" data-k="top10" inputmode="numeric"></div></label>

      <label class="fld"><span class="lab">Nama analisis</span><input class="f" id="label" maxlength="120" placeholder="Misalnya: Laporan tahunan 2025"></label>
      <div style="display:flex;gap:8px;flex-wrap:wrap"><button class="btn" type="submit" id="btnSave">Simpan analisis</button><span class="saved small mute" id="saveMsg" role="status" style="align-self:center"></span></div>
    </form>

    <section class="res" id="res" aria-live="polite"></section>
  </div>
  <section class="noprint" style="display:flex;flex-direction:column;gap:14px;margin-top:18px;border-top:1px solid var(--line);padding-top:26px">
    <div style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap">
      <div style="flex:1;min-width:240px"><p class="mute small" style="margin:0 0 4px;font-weight:700;letter-spacing:.08em;text-transform:uppercase">Analitik lanjutan · melalui konsultasi</p>
        <h2 style="font-size:clamp(20px,2.6vw,26px)">Ketika angka rekap belum cukup</h2>
        <p class="mute" style="margin:6px 0 0;max-width:72ch">Analisis berikut memerlukan data per donatur atau per transaksi, dari ribuan hingga jutaan baris. Data diolah bersama tim Zakademia dengan perjanjian kerahasiaan, identitas donatur dapat disamarkan, dan hasilnya dipresentasikan kepada pimpinan lembaga.</p></div>
      <a class="btn" href="/?topik=Data%20%26%20Analitik#kontak">Jadwalkan konsultasi</a>
    </div>
    <div id="katalog" class="katg"></div>
  </section>
</main>
<script>
var CSRF=<?= json_encode($_SESSION['csrf']) ?>,SAVED=<?= json_encode($saved, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>,ORG=<?= json_encode($u['org'] ?: $u['name'], JSON_HEX_TAG) ?>;
</script>
<script src="/analitik/an.js?v=2"></script>
<script src="/analitik/katalog.js?v=4"></script>
<script src="/js/stat.js?v=2" defer></script>
</body></html>
