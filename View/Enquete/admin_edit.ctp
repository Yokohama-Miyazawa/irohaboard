<?php echo $this->element('menu');?>
<?php echo $this->Html->css('enquete');?>
<div class="enquete-input-header full-view">
  <h1 class="text-center mb-3"><?php echo __('アンケート編集')?></h1>
  <div class="col-sm-12 col-md-8 rounded shadow-lg mx-auto py-5 mb-5">
  <?php
    echo $this->Form->create("Enquete");
		echo $this->Form->hidden('id', array('value' => $edited_enquete['id']));
		echo $this->Form->hidden('user_id', array('value' => $edited_enquete['user_id']));
    echo $this->Form->hidden('before_goal_cleared',	array('value' => $edited_enquete['before_goal_cleared']));
    echo $this->Form->hidden('before_false_reason', array('value' => $edited_enquete['before_false_reason']));
    echo $this->Form->hidden('today_goal', array('value' => $edited_enquete['today_goal']));
    echo $this->Form->hidden('today_goal_cleared', array('value' => $edited_enquete['today_goal_cleared']));
    echo $this->Form->hidden('today_false_reason', array('value' => $edited_enquete['today_false_reason']));
    echo $this->Form->hidden('next_goal', array('value' => $edited_enquete['next_goal']));
    echo $this->Form->hidden('today_impressions', array('value' => $edited_enquete['today_impressions']));
  ?>

  <div class="form-input-block enquete-data">
    <div class="user_name">
      <td><?php echo h($user_info["name"]);?>&nbsp;</td>
    </div>
    <div class = "student-photo">
      <?php
        $img_src = $this->Image->makeInlineImage(Configure::read('student_img').$user_info["pic_path"]);
      ?>
      <img src="<?php echo $img_src; ?>" height="150" alt="<?php echo $pic_path ?>"/>
    </div>
  </div>

  <div class="form-input-block enquete-data">
    授業日：<?php echo h(str_replace('-', '/', Utils::getYMD($edited_enquete['created']))); ?>
  </div>

  <div class="form-input-block enquete-input-group required-input">
  <?php
    echo $this->Form->input('group_id',array(
      'label' => __('担当講師/TA：'),
    	'div' => false,
    	'class' => '',
      'required'=> 'required',
    	'options' => $group_list,
      'empty' => '',
      'value' => $edited_enquete['group_id'],
  	  'style' => ''
    ));
  ?>
  </div>

  <div class="form-input-block enquete-data">
    前回に設定したゴールは達成できたか：<?php echo $edited_enquete['before_goal_cleared']? __("はい") : __("いいえ"); ?>
  </div>

  <?php if(!$edited_enquete['before_goal_cleared']){ ?>
  <div class="form-input-block enquete-data">
    <h4>前回に設定したゴールが達成できなかった理由</h4>
    <p><?php echo h($edited_enquete['before_false_reason']); ?></p>
  </div>
  <?php } ?>

  <div class="form-input-block enquete-data">
    <h4>今日の授業のゴール</h4>
    <p><?php echo h($edited_enquete['today_goal']); ?></p>
  </div>

  <div class="form-input-block enquete-data">
    今日の授業のゴールは達成できたか：<?php echo $edited_enquete['today_goal_cleared']? __("はい") : __("いいえ"); ?>
  </div>

  <?php if(!$edited_enquete['today_goal_cleared']){ ?>
  <div class="form-input-block enquete-data">
    <h4>今日の授業のゴールが達成できなかった理由</h4>
    <p><?php echo h($edited_enquete['today_false_reason']); ?></p>
  </div>
  <?php } ?>

  <div class="form-input-block enquete-data">
    <h4>次回の授業に来る時までに達成するゴール</h4>
    <p><?php echo h($edited_enquete['next_goal']); ?></p>
  </div>

  <div class="form-input-block enquete-data">
    <h4>感想</h4>
    <p><?php echo h($edited_enquete['today_impressions']); ?></p>
  </div>

  <?php
    echo $this->Form->submit(__('更新'),
      array(
        "div" => false,
        "class" => "btn btn-primary",
        'style' => 'margin-left: 20px;',
      )
    );
    echo $this->Form->end();

    echo $this->Form->postLink(__('削除'),
      array(
				'action' => 'delete',
				$edited_enquete['id'],
			),
      array(
        'class' => 'btn btn-danger btn-delete',
        'style' => 'margin-left: 20px; margin-top: 20px; font-size: 110%;',
      ),
      'このアンケートを削除してもよろしいですか?'
    );
  ?>

  </div>
</div>
