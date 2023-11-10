<?php echo $this->element('admin_menu');?>
<?php echo $this->Html->css('attendance');?>
<?php $this->start('script-embedded'); ?>
<script>
$(function () {
	$('[data-toggle="tooltip"]').tooltip({
		boundary: 'window',
		trigger: 'focus hover',
		html: true
	});
});

function downloadCSV()
{
	var url = '<?php echo Router::url(array('action' => 'csv')) ?>/' + $('#MembersEventEventId').val() + '/' + $('#MembersEventStatus').val() + '/' + $('#MembersEventUsername').val();
	$("#EnqueteCmd").val("csv");
	$("#EnqueteAdminIndexForm").submit();
	$("#EnqueteCmd").val("");
}
</script>
<?php $this->end(); ?>
<?php
function getAttendanceIcon($attendance_datum, $is_future=false)
{
	switch($attendance_datum['status']){
		case 0:  // 欠席
			$text_class = 'text-danger';
			$mark  = '×';
			break;
		case 1:  // 出席済
			if($attendance_datum['late_time'] != 0){
				$late_time = $attendance_datum['late_time'];
				$text_class = 'text-success';
				$mark  = '△'."($late_time)";
			}else{
				$text_class = 'text-primary';
				$mark  = '○';
			}
			break;
		case 2:  // 未定
			if($is_future){
				$text_class = 'text-primary';
				$mark  = '-';				
			}else{
				$text_class = 'text-warning';
				$mark  = '?';
			}
			break;
		case 3:  // 遅刻予定
			$text_class = 'text-success';
			$mark  = '△';
			break;
		case 4:  // 早退予定
			$text_class = 'text-primary';
			$mark  = '○(!)';
			break;
	}
	return [
		"mark" => $mark,
		"text-class" => $text_class,
	];
}
?>
<div class="admin-records-index full-view">
	<div class="ib-page-title"><?php echo __('出欠席'); ?></div><br/><br/>

	<!-- 1限の出欠情報 -->
	<div class = "ib-row">
	  <span style = "margin-right : 20px" ><?php echo "受講日：".$last_day;?></span>
		<span style = "margin-right : 20px" id="1st"><?php echo "１限：".$cnt_1."名";?></span>
		<span style = "margin-right : 20px" ><?php echo "出席：".$att_1."名";?></span>
		<span style = "margin-right : 20px" ><?php echo "欠席：".$abs_1."名";?></span>
		<span style = "margin-right : 20px" ><?php echo "出席率：".round(($att_1 / $cnt_1) * 100)."%";?></span>
	</div>

	<!-- 1限の出欠表 -->
	<div class = "record-table" style = "margin-bottom : 20px">
	<table cellpadding="0" cellspacing="0">
		<thead>
			<tr>
				<?php foreach($future_date_list as $date){ echo '<th nowrap class="ib-col-center non-last-column">'.h($date).'</th>'; } ?>
				<th nowrap class="ib-col-center number-column"><?php echo __('No.');?></th>
				<th nowrap class="non-last-column ib-col-center student-number-column"><?php echo __('受講生番号');?></th>
				<th nowrap class="non-last-column"><?php echo __('氏名(学年)');?></th>
			<?php
				$no = 0;
				$length = count($date_list);
				foreach($date_list as $date){
					// 最後の要素
					if(++$no == $length){
						echo '<th nowrap class="ib-col-center last-column">'.h($date).'</th>';
					}else{
						echo '<th nowrap class="ib-col-center non-last-column">'.h($date).'</th>';
					}
				}
			?>
			</tr>
		</thead>
		<tbody>
		<?php
		$tmp_cnt = 1;
		foreach ($period1_members as $member):
			$user_id = $member['id'];
			$future_attendance_info = $future_attendance_list[$user_id];
			$attendance_info = $attendance_list[$user_id];
			$img_src = $this->Html->url(array(
				"controller" => "users",
				"action" => "show_picture",
				$user_id
			), false);
		?>
			<tr>
				<?php
					foreach ($future_attendance_info as $row):
						$attendance_icon = getAttendanceIcon($row['Attendance'], true);
				?>
				<td nowrap class="ib-col-center"><span style = "font-size : 15pt">
					<?php
						$attendance_id = $row['Attendance']['id'];
						echo $this->Html->link(__($attendance_icon['mark']),
							array(
								'controller' => 'attendances',
								'action' => 'admin_edit', $user_id, $attendance_id
							),
							array(
								'class' => $attendance_icon['text-class']
						));
					?>
				</span></td>
				<?php endforeach; ?>
				<td class="ib-col-center"><?php echo h($tmp_cnt++);?></td>
				<td nowrap class="ib-col-center number-column"><?php echo h($member['username']); ?>&nbsp;</td>
				<td nowrap class="student-number-column">
					<span data-toggle="tooltip" title='<img src="<?php echo $img_src; ?>" height="150" alt="<?php echo $member['name']; ?>"/>'>
						<?php
							echo $this->Html->link(h($member['name']).'('.h($member['grade']).')',
								[
									"controller" => "attendances",
									"action" => "admin_student_view", $user_id,
								]
							);
						?>
					</span>
				</td>
				<?php
					foreach ($attendance_info as $row):
						$attendance_icon = getAttendanceIcon($row['Attendance']);
				?>
				<td nowrap class="ib-col-center"><span style = "font-size : 15pt">
					<?php
						$attendance_id = $row['Attendance']['id'];
						echo $this->Html->link(__($attendance_icon['mark']),
							array(
								'controller' => 'attendances',
								'action' => 'admin_edit', $user_id, $attendance_id
							),
							array(
								'class' => $attendance_icon['text-class']
						));
					?>
				</span></td>
				<?php endforeach; ?>
				<?php
					$no_info_number = count($date_list) - count($attendance_info);
					for($i = 0; $i < $no_info_number; $i++){
						echo "<td nowrap>&nbsp;</td>";
					}
				?>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	</div>

	<!-- 2限の出欠情報 -->
	<div class = "ib-row" style = "margin-bottom : 10px">
	  <span style = "margin-right : 20px" ><?php echo "受講日：".$last_day;?></span>
		<span style = "margin-right : 20px" id="2nd"><?php echo "２限：".$cnt_2."名";?></span>
		<span style = "margin-right : 20px" ><?php echo "出席：".$att_2."名";?></span>
		<span style = "margin-right : 20px" ><?php echo "欠席：".$abs_2."名";?></span>
		<span style = "margin-right : 20px" ><?php echo "出席率：".round(($att_2 / $cnt_2) * 100)."%";?></span>
	</div>

	<!-- 2限の出欠表 -->
	<div class = "record-table" style = "margin-top : 30px">
	<table cellpadding="0" cellspacing="0">
		<thead>
			<tr>
				<?php foreach($future_date_list as $date){ echo '<th nowrap class="ib-col-center non-last-column">'.h($date).'</th>'; } ?>
				<th nowrap class="ib-col-center number-column"><?php echo __('No.');?></th>
				<th nowrap class="non-last-column ib-col-center student-number-column"><?php echo __('受講生番号');?></th>
				<th nowrap class="non-last-column"><?php echo __('氏名');?></th>
			<?php
				$no = 0;
				$length = count($date_list);
				foreach($date_list as $date){
					// 最後の要素
					if(++$no == $length){
						echo '<th nowrap class="ib-col-center last-column">'.h($date).'</th>';
					}else{
						echo '<th nowrap class="ib-col-center non-last-column">'.h($date).'</th>';
					}
				}
			?>
			</tr>
		</thead>
		<tbody>
		<?php
		$tmp_cnt = 1;
		foreach ($period2_members as $member):
			$user_id = $member['id'];
			$future_attendance_info = $future_attendance_list[$user_id];
			$attendance_info = $attendance_list[$user_id];
			$img_src = $this->Html->url(array(
				"controller" => "users",
				"action" => "show_picture",
				$user_id
			), false);
		?>
			<tr>
				<?php
					foreach ($future_attendance_info as $row):
						$attendance_icon = getAttendanceIcon($row['Attendance'], true);
				?>
				<td nowrap class="ib-col-center"><span style = "font-size : 15pt">
					<?php
						$attendance_id = $row['Attendance']['id'];
						echo $this->Html->link(__($attendance_icon['mark']),
							array(
								'controller' => 'attendances',
								'action' => 'admin_edit', $user_id, $attendance_id
							),
							array(
								'class' => $attendance_icon['text-class']
						));
					?>
				</span></td>
				<?php endforeach; ?>
				<td class="ib-col-center"><?php echo h($tmp_cnt++);?></td>
				<td nowrap class="ib-col-center number-column"><?php echo h($member['username']); ?>&nbsp;</td>
				<td nowrap class="student-number-column">
					<span data-toggle="tooltip" title='<img src="<?php echo $img_src; ?>" height="150" alt="<?php echo $member['name']; ?>"/>'>
						<?php
							echo $this->Html->link(h($member['name']).'('.h($member['grade']).')',
								[
									"controller" => "attendances",
									"action" => "admin_student_view", $user_id,
								]
							);
						?>
					</span>
				</td>
				<?php
					foreach ($attendance_info as $row):
						$attendance_icon = getAttendanceIcon($row['Attendance']);
				?>
				<td nowrap class="ib-col-center"><span style = "font-size : 15pt">
					<?php
						$attendance_id = $row['Attendance']['id'];
						echo $this->Html->link(__($attendance_icon['mark']),
							array(
								'controller' => 'attendances',
								'action' => 'admin_edit', $user_id, $attendance_id
							),
							array(
								'class' => $attendance_icon['text-class']
						));
					?>
				</span></td>
				<?php endforeach; ?>
				<?php
					$no_info_number = count($date_list) - count($attendance_info);
					for($i = 0; $i < $no_info_number; $i++){
						echo "<td nowrap>&nbsp;</td>";
					}
				?>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	</div>

</div>
