<?php echo $this->element('admin_menu');?>
<div class="admin-groups-index full-view">
	<div class="ib-page-title"><?php echo __('グループ一覧'); ?></div>
	<div class="buttons_container">
		<button type="button" class="btn btn-primary btn-add" onclick="location.href='<?php echo Router::url(array('action' => 'add')) ?>'">+ 追加</button>
	</div>

	<table>
	<thead>
	<tr>
		<th><?php echo $this->Paginator->sort('title', 'グループ名'); ?></th>
		<th class="ib-col-center"><?php echo $this->Paginator->sort('status', '公開/非公開'); ?></th>
		<th class="ib-col-date"><?php echo $this->Paginator->sort('created', '作成日時'); ?></th>
		<th class="ib-col-date"><?php echo $this->Paginator->sort('modified', '更新日時'); ?></th>
		<th class="ib-col-action"><?php echo __('Actions'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($groups as $group): ?>
	<tr>
		<td><?php echo h($group['Group']['title']); ?></td>
		<td class="ib-col-center"><?php echo h(Configure::read('group_status.'.$group['Group']['status'])); ?>&nbsp;</td>
		<td class="ib-col-date"><?php echo h(Utils::getYMDHN($group['Group']['created'])); ?>&nbsp;</td>
		<td class="ib-col-date"><?php echo h(Utils::getYMDHN($group['Group']['modified'])); ?>&nbsp;</td>
		<td class="ib-col-action">
			<?php echo $this->Form->postLink(__('削除'),
					array('action' => 'delete', $group['Group']['id']),
					array('class'=>'btn btn-danger'),
					__('[%s] を削除してもよろしいですか?', $group['Group']['title'])
			); ?>
			<button type="button" class="btn btn-success" onclick="location.href='<?php echo Router::url(array('action' => 'edit', $group['Group']['id'])) ?>'">編集</button>

		</td>
	</tr>
	<?php endforeach; ?>
	</tbody>
	</table>
	<?php /*echo $this->element('paging');*/?>
	<div class="imitated-paging" style="margin-left: 1em;">
		<ul class="pagination">
			<?php
				$this->Paginator->options(array('class' => 'page-link'));
				echo $this->Paginator->numbers(array('currentTag' => 'a class="page-link"'));
			?>
		</ul>
	</div>
</div>
