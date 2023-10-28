<?php echo $this->element('menu');?>
<?php echo $this->Html->css('enquete');?>
<div class="enquete-input-header full-view">
  <h1 class="text-center mb-3"><?php echo __('アンケート'.'('.h($user_info['name']).' '.h(str_replace('-', '/', Utils::getYMD($edited_enquete['created']))) . ' 提出分)'.'編集')?></h1>
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
  <div class="form-input-block enquete-input-group required-input">
  <?php
    echo $this->Form->input('group_id',array(
      'label' => __('個別指導の担当講師：'),
      //'before' => '<label class="col col-sm-3 control-label">コンテンツ種別</label>',
      'after' => '<div class = "text-url-input"></div><span class="status-exp">今日の授業の中で，一番多く指導してくれた講師．</span>',
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

  <input type = "submit" class = "btn btn-primary btn-add" value = "更新">
  <?php echo $this->Form->end(); ?>
  </div>
</div>
