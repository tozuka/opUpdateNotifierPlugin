
<div class="parts">
<div class="partsHeading">
<h3><?php echo $member->name ?> さんの更新通知リクエスト一覧</h3>
</div>
</div>

<ul>
<?php foreach ($requests as $req): ?>
<li><?php echo $req['at'].' '.link_to($req['title'], $req['route']) ?></li>
<?php endforeach ?>
</ul>


