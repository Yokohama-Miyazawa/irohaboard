<!DOCTYPE html>
<html>
	<head>
		<title>プレゼンサイト</title>
		<meta charset="utf-8">
		<script type="text/javascript" src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
		<style type="text/css">
			img#presen {
				height: 85%;
				width: 85%;
			}
		</style>
		<script type="text/javascript">
		var count, stopped,
			date, SLIDE, SRC,
			voice,
			textData, showText, i, wait
		count = 1; stopped = true
		date = new Date()
    SLIDE = '<?php echo $slide_name; ?>'
    SRC = '<?php echo $this->webroot.'slide/'?>' + SLIDE + '/'
    console.log(SRC);
		voice = new Audio()
		$.ajax(SRC + SLIDE + '-scenario.txt', 'post').done(function (beforeData) { textData = beforeData.split('\n') })
		showText = function () {
			wait = 150
			if ('，．, .'.indexOf(textData[count - 1][i]) != -1) {
				wait = 600
			}
			$('span#text')[0].innerText += textData[count - 1][i]; i++
			if (i >= textData[count - 1].length) {
				$('button#next')[0].innerText = '次へ'
				stopped = true; count++
			} else {
				setTimeout(showText, wait)
			}
		}
		window.onload = function () {
			$('button#next')[0].onclick = function () {
				if (!stopped) { console.log('No!') } else {
					if (textData[count - 1] == undefined) { count = 1 }
					$('button#next')[0].innerText = '...'
					stopped = false
					$('img#presen')[0].src = SRC + ('000' + count).slice(-3) + '.jpeg'
          voice.src = '<?php echo $this->webroot ?>' + '/contents_questions/play_sound/' + textData[count - 1]
					voice.load(); voice.play()
					$('span#text')[0].innerText = ''
					i = 0
					showText()
				}
			}
		}
		</script>
	</head>
	<body>
    <?php echo $this->Html->image('TestImage.jpg', array(
      'id'  => 'presen',
      'alt' => 'スライドがここに表示されます'
    )); ?>
    <br>
		<span id="text"></span><br>
		<button id="next">クリックして始める</button>
	</body>
</html>
