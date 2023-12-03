<?php echo $this->element('admin_menu');?>
<?php echo $this->Html->css('custom');?>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

<script type="text/javascript">
  google.charts.load("current", {packages:["corechart"]});
  google.charts.setOnLoadCallback(() => {
    // 1st
    drawChart(
      <?php echo $attended_users['1st']['Whole']['Count'];?>,
      <?php echo $absent_users['1st']['Whole']['Count'];?>,
      "<?php echo $attended_users['1st']['Whole']['Member'];?>",
      "<?php echo $absent_users['1st']['Whole']['Member'];?>",
      "period1WholeChart"
    );
    drawChart(
      <?php echo $attended_users['1st']['Face']['Count'];?>,
      <?php echo $absent_users['1st']['Face']['Count'];?>,
      "<?php echo $attended_users['1st']['Face']['Member'];?>",
      "<?php echo $absent_users['1st']['Face']['Member'];?>",
      "period1FaceChart"
    );
    drawChart(
      <?php echo $attended_users['1st']['Online']['Count'];?>,
      <?php echo $absent_users['1st']['Online']['Count'];?>,
      "<?php echo $attended_users['1st']['Online']['Member'];?>",
      "<?php echo $absent_users['1st']['Online']['Member'];?>",
      "period1OnlineChart"
    );

    // 2nd
    drawChart(
      <?php echo $attended_users['2nd']['Whole']['Count'];?>,
      <?php echo $absent_users['2nd']['Whole']['Count'];?>,
      "<?php echo $attended_users['2nd']['Whole']['Member'];?>",
      "<?php echo $absent_users['2nd']['Whole']['Member'];?>",
      "period2WholeChart"
    );
    drawChart(
      <?php echo $attended_users['2nd']['Face']['Count'];?>,
      <?php echo $absent_users['2nd']['Face']['Count'];?>,
      "<?php echo $attended_users['2nd']['Face']['Member'];?>",
      "<?php echo $absent_users['2nd']['Face']['Member'];?>",
      "period2FaceChart"
    );
    drawChart(
      <?php echo $attended_users['2nd']['Online']['Count'];?>,
      <?php echo $absent_users['2nd']['Online']['Count'];?>,
      "<?php echo $attended_users['2nd']['Online']['Member'];?>",
      "<?php echo $absent_users['2nd']['Online']['Member'];?>",
      "period2OnlineChart"
    );

    changeDisplayList({'id':'whole'});
  });

  function drawChart(attended_count, absent_count, atended_member, absent_member, element) {
    let data = new google.visualization.DataTable();
    data.addColumn('string', 'Status');
    // Use custom HTML content for the domain tooltip.
    data.addColumn('number', 'Number');
    data.addColumn({'type': 'string', 'role': 'tooltip', 'p': {'html': true}});

    const attended_students = attended_count >= 10 ? "" : "<p style='font-size : 20px; white-space : nowrap;'>" + atended_member + "</p>";
    const absent_students = "<p style='font-size : 20px; white-space : nowrap;'>" + absent_member + "</p>";
    data.addRows([
      ['出席数',attended_count, attended_students],
      ['欠席数',absent_count, absent_students],
    ])
    const options = {
      pieHole: 0.55,
      fontSize: 18,
      legend: { position: 'top', alignment: 'center' },
      'chartArea': {'width': '100%', 'height': '75%'},
      tooltip: { isHtml: true }
    }
    const chart = new google.visualization.PieChart(document.getElementById(element));
    chart.draw(data, options);
  }

  function changeDisplayList(obj){
    let wholeCharts = document.getElementsByClassName('whole');
    let separateCharts = document.getElementsByClassName('separate');
    if(obj['id'] == 'whole'){
      for(let i=0; i < wholeCharts.length; i++){ wholeCharts[i].hidden = false; }
      for(let i=0; i < separateCharts.length; i++){ separateCharts[i].hidden = true; }
	  }else if(obj['id'] == 'separate'){
      for(let i=0; i < wholeCharts.length; i++){ wholeCharts[i].hidden = true; }
      for(let i=0; i < separateCharts.length; i++){ separateCharts[i].hidden = false; }
    }
  }
</script>

<div class="admin-submission-status full-view">
  <div class="row">
    <div class="col" style = "font-size : 24px;">
      <?php echo __('出席状況'); ?>
    </div>
  </div>
  <div class="row">
    <div class="col">
      <h5><?php echo __('授業日:').$last_day;?></h5>
    </div>
  </div>
  <div class="row text-center">
    <div class="col">
      <input type="radio" name="list-radio" id="whole" value="user_list" onClick="changeDisplayList(this)" checked>
      <label for="whole">全体</label>
      <input type="radio" name="list-radio" id="separate" value="admin_list" onClick="changeDisplayList(this)">
      <label for="separate">対面/オンライン別</label>
    </div>
  </div>

  <div class="card">
    <div class = "card-header">
      <div class="text-center" style="font-size:18px;">
        １限 
        人数: <?php echo ($attended_users['1st']['Whole']['Count'] + $absent_users['1st']['Whole']['Count']);?>人, 
        出席: <?php echo ($attended_users['1st']['Whole']['Count']);?>人, 
        欠席: <?php echo ($absent_users['1st']['Whole']['Count']);?>人
      </div>
    </div>

    <div class="card-body">
      <div class="row whole">
        <div class="col">
          <div class="card" id="chart" style="border: none;" onclick="location.href='<?php echo Router::url(array('controller' => 'attendances', 'action' => 'index')) ?>'">
            <div class="card-body" id="chart-body">
              <div class="pie-chart" id="period1WholeChart"></div>
              <div class="labelOverlay">
                <p class="total-caption">1限全体</p>
                <p class="total-value"><?php echo ($attended_users['1st']['Whole']['Count'] + $absent_users['1st']['Whole']['Count']);?>人</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row separate">
        <div class="col">
          <div class="card" id="chart" style="border: none;" onclick="location.href='<?php echo Router::url(array('controller' => 'attendances', 'action' => 'index')) ?>'">
            <div class="card-body" id="chart-body">
              <div class="pie-chart" id="period1FaceChart"></div>
              <div class="labelOverlay">
                <p class="total-caption">対面</p>
                <p class="total-value"><?php echo ($attended_users['1st']['Face']['Count'] + $absent_users['1st']['Face']['Count']);?>人</p>
              </div>
            </div>
          </div>
        </div>
        <div class="col">
          <div class="card" id="chart" style="border: none;"onclick="location.href='<?php echo Router::url(array('controller' => 'attendances', 'action' => 'index')) ?>'">
            <div class="card-body" id="chart-body">
              <div class="pie-chart" id="period1OnlineChart"></div>
              <div class="labelOverlay">
                <p class="total-caption">オンライン</p>
                <p class="total-value"><?php echo ($attended_users['1st']['Online']['Count'] + $absent_users['1st']['Online']['Count']);?>人</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class = "card-header">
      <div class="text-center" style="font-size:18px;">
        2限 
        人数: <?php echo ($attended_users['2nd']['Whole']['Count'] + $absent_users['2nd']['Whole']['Count']);?>人, 
        出席: <?php echo ($attended_users['2nd']['Whole']['Count']);?>人, 
        欠席: <?php echo ($absent_users['2nd']['Whole']['Count']);?>人
      </div>
    </div>

    <div class="card-body">
      <div class="row whole">
        <div class="col">
          <div class="card" id="chart" style="border: none;" onclick="location.href='<?php echo Router::url(array('controller' => 'attendances', 'action' => 'index', '#' => '2nd')) ?>'">
            <div class="card-body" id="chart-body">
              <div class="pie-chart" id="period2WholeChart"></div>
              <div class="labelOverlay">
                <p class="total-caption">2限全体</p>
                <p class="total-value"><?php echo ($attended_users['2nd']['Whole']['Count'] + $absent_users['2nd']['Whole']['Count']);?>人</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row separate">
        <div class="col">
          <div class="card" id="chart" style="border: none;" onclick="location.href='<?php echo Router::url(array('controller' => 'attendances', 'action' => 'index', '#' => '2nd')) ?>'">
            <div class="card-body" id="chart-body">
              <div class="pie-chart" id="period2FaceChart"></div>
              <div class="labelOverlay">
                <p class="total-caption">対面</p>
                <p class="total-value"><?php echo ($attended_users['2nd']['Face']['Count'] + $absent_users['2nd']['Face']['Count']);?>人</p>
              </div>
            </div>
          </div>
        </div>
        <div class="col">
          <div class="card" id="chart" style="border: none;" onclick="location.href='<?php echo Router::url(array('controller' => 'attendances', 'action' => 'index', '#' => '2nd')) ?>'">
            <div class="card-body" id="chart-body">
              <div class="pie-chart" id="period2OnlineChart"></div>
              <div class="labelOverlay">
                <p class="total-caption">オンライン</p>
                <p class="total-value"><?php echo ($attended_users['2nd']['Online']['Count'] + $absent_users['2nd']['Online']['Count']);?>人</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>
