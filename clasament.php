<?php
require __DIR__ . '/core.php';
$board = leaderboard_rows(30);
page_header('Clasament', 'clasament');
?>
<section class="section page-top">
  <div class="container narrow">
    <div class="section-head">
      <div class="eyebrow">Clasament</div>
      <h1>Punctele câștigate din lecții și teste</h1>
    </div>
    <section class="panel">
      <div class="leaderboard-table full">
        <?php foreach ($board as $i => $item): ?>
        <div class="leader-row">
          <div class="leader-row-rank">#<?php echo $i + 1; ?></div>
          <div class="leader-row-name"><?php echo $item['icon']; ?> <?php echo e($item['full_name']); ?></div>
          <div class="leader-row-role"><?php echo e(ucfirst($item['role'])); ?> · <?php echo e($item['title']); ?></div>
          <div class="leader-row-score"><?php echo $item['xp']; ?> puncte</div>
        </div>
        <?php endforeach; ?>
      </div>
    </section>
  </div>
</section>
<?php page_footer(); ?>
