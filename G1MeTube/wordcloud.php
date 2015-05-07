<?php
require_once "models/WordCloud.php";
require_once "models/Account.php";
require_once "function.php";
if(!isset($_SESSION)){
	session_start();
}

if(isset($_SESSION['myAccount'])){ // logged in, get user info
	$myAccount = unserialize($_SESSION['myAccount']);
}

$wordcloud = new WordCloud();
?>

<html>
	<head>
		<title></title>
		<link rel="stylesheet" href="css/reset.css" />
		<link rel="stylesheet" href="css/text.css" />
		<link rel="stylesheet" href="css/960_12_col.css" />
		<link rel="stylesheet" href="css/mainstyle.css" />
	</head>
	<body>
		<!-- container is a wrapper for all main sections, and defines bounds of
		any content on the screen -->
		<div id="container" class="container_12">

		<!-- IMPORT HEADER -->
		<?php require_once 'partials/header.php' ?>

		<!-- CONTENT REGION -->
		<div id="content" class="grid_12" style="text-align: center">


		</div> <!-- End Content -->
		<!-- IMPORT FOOTER -->
		<?php require_once 'partials/footer.php' ?>

		<script src="lib/d3.js"></script>
		<script src="lib/d3.layout.cloud.js"></script>
		<script>
		var frequency_list = <?php echo $wordcloud->getString();?>;
		var fill = d3.scale.category20();

			d3.layout.cloud().size([800, 600])
			.words(frequency_list)
		.rotate(0)
			.font("Impact")
			.fontSize(function(d) { return d.size; })
			.on("end", draw)
			.start();

			function draw(words) {
				d3.select("#content").append("svg")
					.attr("width", 850)
					.attr("height", 650)
					.attr("align","center")
					.append("g")
					.attr("transform", "translate(425,325)")
					.selectAll("text")
					.data(words)
					.enter().append("text")
					.style("font-size", function(d) { return d.size + "px"; })
					.style("font-family", "Impact")
					.style("fill", function(d, i) { return fill(i); })
					.attr("text-anchor", "middle")
					.attr("transform", function(d) {
							return "translate(" + [d.x, d.y] + ")rotate(" + d.rotate + ")";
							})
				.text(function(d) { return d.text; });
			}
		</script>

		</div>
	</body>
</html>
