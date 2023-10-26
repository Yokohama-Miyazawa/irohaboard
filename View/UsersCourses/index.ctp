<?php echo $this->element('menu');?>
<?php echo $this->Html->css('user_course.css?231026');?>
<script>
	function checkEnquete(){
		if(document.getElementById("today_goal").value == ""){
			alert("今日の授業のゴールを書いてください");
			return false;
		}else{
			return true;
		}
	}
	function checkAbsence(){
		if(document.getElementById("AttendanceReason").value == ""){
			alert("欠席理由を書いてください");
			return false;
		}else{
			return true;
		}
	}
</script>

<div class="users-courses-index full-view">

	<?php if(true/*$have_to_write_today_goal*/){ ?>
		<div class="modal js-modal">
			<div class="modal__bg"></div>
			<div class="modal__content tab-wrap">
				<input id="TAB-ATTEND" type="radio" name="TAB" class="tab-switch" checked="checked" />
					<label class="tab-label" id="attend-label" for="TAB-ATTEND">出席</label>
				<div class="tab-content attend">
					<p>今日の担当講師を選び、今日の授業のゴールを書いてください。これを送信すると出席扱いになります。</p>
					<?php
					echo $this->Form->create(false,['type' => 'post','url'=> ['controller' => 'enquete','action' => 'index'],'novalidate' => true]);
					echo $this->Form->hidden('group_id', array('value' => $group_id));

					echo $this->Form->hidden('next_goal', array('value' => ''));
					echo $this->Form->hidden('today_impressions', array('value' => ''));

					echo "<div class='form-input-block enquete-input-group required-input'>";
					echo $this->Form->input('group_id',array(
						'label' => __('個別指導の担当講師：'),
						'div' => false,
						'class' => '',
						'required'=> 'required',
						'options' => $group_list,
						'empty' => '',
						'value' => $enquete_inputted['Enquete']['group_id'],
						'style' => ''
					));
					echo "</div>";

					echo "<div class = 'form-input-block today-goal required-input ' >";
					echo $this->Form->input('today_goal', array(
						'label' => __('今日の授業のゴールを書いてください。'),
						'type' => 'textarea',
						'div' => false,
						'class' => '',
						'required'=> 'required',
						'style' => '',
						'value' => $enquete_inputted['Enquete']['today_goal']
					));
					echo "</div>";
					?>
					<input type="submit" class="btn btn-info btn-add" value="送信" onclick="return checkEnquete()">
					<?php echo $this->Form->end(); ?>
				</div>
				<input id="TAB-ABSENT" type="radio" name="TAB" class="tab-switch" />
					<label class="tab-label" id="absent-label" for="TAB-ABSENT">欠席</label>
				<div class="tab-content absent">
					<?php
					echo $this->Form->create(false, ['type' => 'post','url'=> ['controller' => 'attendances','action' => 'edit', $today_attendance_id], 'novalidate' => true]);
					echo $this->Form->hidden('Attendance.status', array('value' => 0));
					echo $this->Form->input('Attendance.reason', array(
					  'label' => __('欠席理由を書いてください。'),
					  'type' => 'textarea',
					  'value' => $attendance_reason,
					  'div' => false,
					  'required'=> true,
					  'class' => '',
					  'style' => '',
					));
					?>
					<input type="submit" class="btn btn-info" value="送信" onclick="return checkAbsence()">
					<?php echo $this->Form->end(); ?>
				</div>
			</div>
		</div>
	<?php } ?>

	<?php if($role === "user"){ ?>
		<div class = "attendance-block my-4 ">
			<div class = "attendance-info">
		    	<div class = "attendance-date-block rounded shadow-lg">
					<div class = "attendance-date">
		       			<?php foreach($user_info as $row):?>
		        			<div class = "date">
		           			<?php
								$class_date = (new DateTime($row['Date']['date']))->format('m月d日');
								if(strtotime($row['Date']['date']) >= strtotime(date('Y-m-d'))){
									 $attendance_id = $row['Attendance']['id'];
									 echo $this->Html->link($class_date, array('controller' => 'attendances', 'action' => 'edit', $attendance_id));
								}else{
		            				echo h($class_date);
							 	}
		        			?>
		         			</div>
						<?php endforeach;?>
					</div>
					<div class = "attendance-status">
						<?php foreach($user_info as $row):?>
							<div class = "status">
		        			<?php
								if(strtotime($row['Date']['date']) >= strtotime(date('Y-m-d'))){
									switch($row['Attendance']['status']){
    									case 0:
    	    								echo __("欠席");
    	    								break;
										case 1:
											echo __("出席済");
											break;
    									case 3:
    	    								echo __("遅刻");
    	    								break;
    									case 4:
    	    								echo __("早退");
    	    								break;
										default:
											echo __("出席予定");
											break;
									}
								}else{
		            				if($row['Attendance']['status'] != 1){
		            	  				echo h('欠席');
		            				}else{
		            	  				echo h('出席');
		            				}
								}
		        			?>
		        			</div>
						<?php endforeach;?>
					</div>
				</div>
			</div>
		</div>
	<?php } ?>

	<?php if(count($infos) > 0){?>
		<div class="card bg-light mb-4 rounded shadow">
			<div class="card-body">
				<table cellpadding="0" cellspacing="0">
					<tbody>
						<?php foreach ($infos as $an_info): ?>
							<tr>
								<td><?php echo $this->Html->link($an_info['Info']['title'], array('controller' => 'infos', 'action' => 'view', $an_info['Info']['id'])); ?></td>
								<td width="150" valign="top"><?php echo (new DateTime($an_info['Info']['created']))->format('Y年m月d日'); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<div class="text-right"><?php echo $this->Html->link(__('一覧を表示'), array('controller' => 'infos', 'action' => 'index')); ?></div>
			</div>
		</div>
	<?php }?>

	<div class="card bg-light mb-4 rounded shadow">
		<div class="card-header"><?php echo __('全体のお知らせ'); ?></div>
		<div class="card-body">
			<?php if($info!=""){?>
				<div class="mb-4">
					<?php
						$info = $this->Text->autoLinkUrls($info, array( 'target' => '_blank'));
						$info = nl2br($info);
						echo $info;
					?>
				</div>
			<?php } else {
				echo $no_info;
			}?>
		</div>
	</div>

	<?php if($next_goal){?>
	<div class="card border-light rounded shadow-lg">
  	<div class="card-header">次回の授業に来る時までに達成するゴール</div>
  	<div class="card-body">
    	<?php echo h($next_goal); ?>
  	</div>
	</div>
	<?php }?>

	<div class="card border-light">
		<div class="card-header"><?php echo __('コース一覧'); ?></div>
		<div class="card-body">
			<div class="accordion">
				<?php foreach($categories as $category):?>
				<?php
				  	// コースがない時、非表示 
					if(count($categories_and_courses[$category["id"]]) == 0){
						continue;
					}
				?>
				<div class="card mb-3 shadow rounded-lg">
      			<div class="card-header" id='heading-<?php echo $category['id']?>'>
        			<h5 class="mb-0">
          			<button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse-<?php echo $category['id']?>" aria-expanded="true" aria-controls="collapse-<?php echo $category['id']?>">
            			<?php echo $category['title']?>
          			</button>
        			</h5>
      			</div>

				<div id="collapse-<?php echo $category['id']?>" class="collapse" aria-labelledby="heading-<?php echo $category['id']?>'">
      			  	<div class="card-body">
      			    	<ul class="list-group rounded">
							<?php foreach($categories_and_courses[$category["id"]] as $course_info):?>
								<?php $left_cnt = $course_info['sum_cnt'] - $course_info['did_cnt'];?>
								<?php $bar_percent = round(($course_info['did_cnt'] / $course_info['sum_cnt']) * 100 );?>
								<a href="<?php echo Router::url(array('controller' => 'contents', 'action' => 'index', $course_info['id']));?>" class="list-group-item">
									<h4 class="list-group-item-heading"><?php echo h($course_info['title']);?></h4>
									<p class="list-group-item-text">
										<span style="color:black"><?php echo __('学習開始日').': '.Utils::getYMD($course_info['first_date']); ?></span>
										<span style="color:black"><?php echo __('最終学習日').': '.Utils::getYMD($course_info['last_date']); ?></span>
									</p>
									<div class="progress">
  									<div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="<?php echo $bar_percent;?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $bar_percent?>%"></div>
									</div>
								</a>
							<?php endforeach;?>
						</ul>
      				</div>
      			</div>
				</div>
				<?php endforeach;?>
			</div>
		</div>
	</div>

</div>
